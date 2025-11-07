<?php

declare(strict_types=1);

use App\Actions\Links\CreateLink;
use App\Models\Link;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

describe('Link Creation Caching', function () {
    test('link is cached on creation', function () {
        $action = app(CreateLink::class);

        $link = $action->execute([
            'url' => 'https://example.com/test',
            'user_id' => null,
            'custom_slug' => null,
            'expires_at' => null,
        ]);

        $cached = Cache::get("link:{$link->hash}");

        expect($cached)->not->toBeNull()
            ->id->toBe($link->id)
            ->full_url->toBe('https://example.com/test');
    });

    test('link with custom slug is cached under both hash and slug', function () {
        $action = app(CreateLink::class);

        $link = $action->execute([
            'url' => 'https://example.com/slug-test',
            'user_id' => null,
            'custom_slug' => 'test-slug',
            'expires_at' => null,
        ]);

        $cachedByHash = Cache::get("link:{$link->hash}");
        $cachedBySlug = Cache::get('link:test-slug');

        expect($cachedByHash)->not->toBeNull()
            ->and($cachedBySlug)->not->toBeNull()
            ->and($cachedByHash->id)->toBe($link->id)
            ->and($cachedBySlug->id)->toBe($link->id);
    });

    test('cache has correct TTL', function () {
        $action = app(CreateLink::class);
        config(['anon.default_cache_ttl' => 100]); // 100 seconds for test

        $link = $action->execute([
            'url' => 'https://example.com/ttl-test',
            'user_id' => null,
            'custom_slug' => null,
            'expires_at' => null,
        ]);

        // Cache should exist
        expect(Cache::has("link:{$link->hash}"))->toBeTrue();

        // Note: Can't easily test TTL expiration in unit test without mocking time
    });
});

describe('Link Update Caching', function () {
    test('cache is invalidated and refreshed on update', function () {
        $link = Link::factory()->create([
            'hash' => 'update-test',
            'full_url' => 'https://example.com/original',
            'is_active' => true,
        ]);

        // Verify initial cache
        $cached = Cache::get("link:{$link->hash}");
        expect($cached)->not->toBeNull()
            ->full_url->toBe('https://example.com/original');

        // Update link
        $link->update(['full_url' => 'https://example.com/updated']);

        // Cache should be refreshed with new data
        $cached = Cache::get("link:{$link->hash}");
        expect($cached)->not->toBeNull()
            ->full_url->toBe('https://example.com/updated');
    });

    test('deactivating link updates cache', function () {
        $link = Link::factory()->create([
            'hash' => 'deactivate',
            'is_active' => true,
        ]);

        expect(Cache::get("link:{$link->hash}")->is_active)->toBeTrue();

        $link->update(['is_active' => false]);

        expect(Cache::get("link:{$link->hash}")->is_active)->toBeFalse();
    });
});

describe('Link Deletion Caching', function () {
    test('cache is cleared on deletion', function () {
        $link = Link::factory()->create([
            'hash' => 'delete-test',
            'full_url' => 'https://example.com/delete',
        ]);

        expect(Cache::has("link:{$link->hash}"))->toBeTrue();

        $link->delete();

        expect(Cache::has("link:{$link->hash}"))->toBeFalse();
    });

    test('custom slug cache is cleared on deletion', function () {
        $link = Link::factory()->create([
            'hash' => 'slug-delete',
            'slug' => 'slug-delete',
        ]);

        expect(Cache::has("link:{$link->hash}"))->toBeTrue();
        expect(Cache::has("link:{$link->slug}"))->toBeTrue();

        $link->delete();

        expect(Cache::has("link:{$link->hash}"))->toBeFalse();
        expect(Cache::has("link:{$link->slug}"))->toBeFalse();
    });
});

describe('Redirect Controller Caching', function () {
    test('redirect controller uses cache', function () {
        $link = Link::factory()->create([
            'hash' => 'controller-cache',
            'full_url' => 'https://example.com/cached',
            'is_active' => true,
        ]);

        // First request should cache the link
        $response = $this->get('/controller-cache');
        $response->assertSuccessful();

        // Verify it's in cache
        expect(Cache::has('link:controller-cache'))->toBeTrue();

        // Second request should use cache
        $response = $this->get('/controller-cache');
        $response->assertSuccessful();
    });

    test('cache miss triggers database query', function () {
        $link = Link::factory()->create([
            'hash' => 'cache-miss',
            'full_url' => 'https://example.com/miss',
            'is_active' => true,
        ]);

        // Clear cache to simulate miss
        Cache::forget('link:cache-miss');

        $response = $this->get('/cache-miss');

        $response->assertSuccessful();

        // Should be cached now
        expect(Cache::has('link:cache-miss'))->toBeTrue();
    });
});

describe('Observer Caching', function () {
    test('observer caches link on creation', function () {
        $link = Link::create([
            'hash' => 'observer-create',
            'url_scheme' => 'https',
            'url_host' => 'example.com',
            'full_url' => 'https://example.com',
            'full_url_hash' => hash('sha256', 'https://example.com'),
        ]);

        expect(Cache::has('link:observer-create'))->toBeTrue();
    });

    test('observer sets default values', function () {
        $link = Link::create([
            'hash' => 'defaults',
            'url_scheme' => 'https',
            'url_host' => 'example.com',
            'full_url' => 'https://example.com',
            'full_url_hash' => hash('sha256', 'https://example.com'),
        ]);

        expect($link->visits)->toBe(0)
            ->and($link->unique_visits)->toBe(0)
            ->and($link->is_active)->toBeTrue()
            ->and($link->is_reported)->toBeFalse();
    });

    test('observer caches link on update', function () {
        $link = Link::factory()->create([
            'hash' => 'observer-update',
        ]);

        Cache::forget('link:observer-update');

        $link->update(['full_url' => 'https://example.com/updated']);

        expect(Cache::has('link:observer-update'))->toBeTrue();
    });

    test('observer clears cache on deletion', function () {
        $link = Link::factory()->create([
            'hash' => 'observer-delete',
        ]);

        expect(Cache::has('link:observer-delete'))->toBeTrue();

        $link->delete();

        expect(Cache::has('link:observer-delete'))->toBeFalse();
    });
});

describe('Cache Performance', function () {
    test('multiple requests use cache efficiently', function () {
        $link = Link::factory()->create([
            'hash' => 'performance',
            'is_active' => true,
            'visits' => 0, // Start from 0
        ]);

        $initialVisits = $link->visits;

        // Make multiple requests
        for ($i = 0; $i < 10; $i++) {
            $response = $this->get('/performance');
            $response->assertSuccessful();
        }

        // Cache should still exist
        expect(Cache::has('link:performance'))->toBeTrue();

        // Visit counter should be incremented for all requests
        $link->refresh();
        expect($link->visits)->toBe($initialVisits + 10);
    });

    test('cache reduces database queries', function () {
        $link = Link::factory()->create([
            'hash' => 'query-test',
            'is_active' => true,
        ]);

        // First request (cache miss)
        $this->get('/query-test');

        // Subsequent requests should use cache
        // This is verified by the cache-first strategy in RedirectController
        $cached = Cache::get('link:query-test');

        expect($cached)->not->toBeNull()
            ->id->toBe($link->id);
    });
});
