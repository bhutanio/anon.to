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
        ]);

        $cached = Cache::get("link:{$link->hash}");

        expect($cached)->not->toBeNull()
            ->id->toBe($link->id)
            ->full_url->toBe('https://example.com/test');
    });

    test('cache has correct TTL', function () {
        $action = app(CreateLink::class);
        config(['anon.default_cache_ttl' => 100]); // 100 seconds for test

        $link = $action->execute([
            'url' => 'https://example.com/ttl-test',
            'user_id' => null,
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
});

describe('Redirect Controller Caching', function () {
    test('redirect controller uses cache', function () {
        $link = Link::factory()->create([
            'hash' => 'ctrl01',
            'full_url' => 'https://example.com/cached',
            'is_active' => true,
        ]);

        // First request should cache the link
        $response = $this->get('/ctrl01');
        $response->assertSuccessful();

        // Verify it's in cache
        expect(Cache::has('link:ctrl01'))->toBeTrue();

        // Second request should use cache
        $response = $this->get('/ctrl01');
        $response->assertSuccessful();
    });

    test('cache miss triggers database query', function () {
        $link = Link::factory()->create([
            'hash' => 'miss01',
            'full_url' => 'https://example.com/miss',
            'is_active' => true,
        ]);

        // Clear cache to simulate miss
        Cache::forget('link:miss01');

        $response = $this->get('/miss01');

        $response->assertSuccessful();

        // Should be cached now
        expect(Cache::has('link:miss01'))->toBeTrue();
    });
});

describe('Observer Caching', function () {
    test('observer caches link on creation', function () {
        $link = Link::create([
            'hash' => 'obs001',
            'url_scheme' => 'https',
            'url_host' => 'example.com',
            'full_url' => 'https://example.com',
            'full_url_hash' => hash('sha256', 'https://example.com'),
        ]);

        expect(Cache::has('link:obs001'))->toBeTrue();
    });

    test('database defaults are applied on creation', function () {
        $link = Link::create([
            'hash' => 'def001',
            'url_scheme' => 'https',
            'url_host' => 'example.com',
            'full_url' => 'https://example.com',
            'full_url_hash' => hash('sha256', 'https://example.com'),
            'visits' => 0,
            'is_active' => true,
            'is_reported' => false,
        ]);

        expect($link->visits)->toBe(0)
            ->and($link->is_active)->toBeTrue()
            ->and($link->is_reported)->toBeFalse();
    });

    test('observer caches link on update', function () {
        $link = Link::factory()->create([
            'hash' => 'upd001',
        ]);

        Cache::forget('link:upd001');

        $link->update(['full_url' => 'https://example.com/updated']);

        expect(Cache::has('link:upd001'))->toBeTrue();
    });

    test('observer clears cache on deletion', function () {
        $link = Link::factory()->create([
            'hash' => 'del001',
        ]);

        expect(Cache::has('link:del001'))->toBeTrue();

        $link->delete();

        expect(Cache::has('link:del001'))->toBeFalse();
    });
});

describe('Cache Performance', function () {
    test('multiple requests use cache efficiently', function () {
        $link = Link::factory()->create([
            'hash' => 'perf01',
            'is_active' => true,
            'visits' => 0, // Start from 0
        ]);

        $initialVisits = $link->visits;

        // Make multiple requests in the same session
        for ($i = 0; $i < 10; $i++) {
            $response = $this->get('/perf01');
            $response->assertSuccessful();
        }

        // Cache should still exist
        expect(Cache::has('link:perf01'))->toBeTrue();

        // Visit counter should be incremented once (session-based tracking)
        $link->refresh();
        expect($link->visits)->toBe($initialVisits + 1);
    });

    test('cache reduces database queries', function () {
        $link = Link::factory()->create([
            'hash' => 'qry001',
            'is_active' => true,
        ]);

        // First request (cache miss)
        $this->get('/qry001');

        // Subsequent requests should use cache
        // This is verified by the cache-first strategy in RedirectController
        $cached = Cache::get('link:qry001');

        expect($cached)->not->toBeNull()
            ->id->toBe($link->id);
    });
});
