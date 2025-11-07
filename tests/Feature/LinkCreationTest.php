<?php

declare(strict_types=1);

use App\Actions\Links\CreateLink;
use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

describe('Anonymous Link Creation', function () {
    test('anonymous user can create link', function () {
        $action = app(CreateLink::class);

        $link = $action->execute([
            'url' => 'https://example.com/page',
            'user_id' => null,
            'expires_at' => null,
        ]);

        expect($link)->toBeInstanceOf(Link::class)
            ->full_url->toBe('https://example.com/page')
            ->hash->toHaveLength(6)
            ->user_id->toBeNull()
            ->is_active->toBeTrue();

        $this->assertDatabaseHas('links', [
            'full_url' => 'https://example.com/page',
            'is_active' => true,
        ]);
    });

    test('creates link with complex URL', function () {
        $action = app(CreateLink::class);

        $complexUrl = 'https://example.com:8080/path/to/page?query=value&another=param#section';

        $link = $action->execute([
            'url' => $complexUrl,
            'user_id' => null,
            'expires_at' => null,
        ]);

        expect($link->url_scheme)->toBe('https')
            ->and($link->url_host)->toBe('example.com')
            ->and($link->url_port)->toBe(8080)
            ->and($link->url_path)->toBe('/path/to/page')
            ->and($link->url_query)->toBe('query=value&another=param')
            ->and($link->url_fragment)->toBe('section')
            ->and($link->full_url)->toBe($complexUrl);
    });

    test('link is automatically cached after creation', function () {
        $action = app(CreateLink::class);

        $link = $action->execute([
            'url' => 'https://example.com/cached-link',
            'user_id' => null,
            'expires_at' => null,
        ]);

        $cached = Cache::get("link:{$link->hash}");

        expect($cached)->not->toBeNull()
            ->id->toBe($link->id)
            ->full_url->toBe('https://example.com/cached-link');
    });
});

describe('Registered User Link Creation', function () {
    test('registered user can create link', function () {
        $user = User::factory()->create();
        $action = app(CreateLink::class);

        $link = $action->execute([
            'url' => 'https://example.com/user-page',
            'user_id' => $user->id,
            'expires_at' => null,
        ]);

        expect($link->user_id)->toBe($user->id)
            ->and($link->full_url)->toBe('https://example.com/user-page');

        $this->assertDatabaseHas('links', [
            'user_id' => $user->id,
            'full_url' => 'https://example.com/user-page',
        ]);
    });

    test('registered user can set expiration date', function () {
        $user = User::factory()->create();
        $action = app(CreateLink::class);
        $expiresAt = now()->addDays(30);

        $link = $action->execute([
            'url' => 'https://example.com/expires',
            'user_id' => $user->id,
            'expires_at' => $expiresAt->toDateTimeString(),
        ]);

        expect($link->expires_at)->not->toBeNull()
            ->and($link->expires_at->timestamp)->toBe($expiresAt->timestamp);
    });
});

describe('Duplicate Detection', function () {
    test('returns existing link for duplicate URL', function () {
        $action = app(CreateLink::class);
        $url = 'https://example.com/duplicate-test';

        // Create first link
        $firstLink = $action->execute([
            'url' => $url,
            'user_id' => null,
            'expires_at' => null,
        ]);

        // Attempt to create duplicate
        $secondLink = $action->execute([
            'url' => $url,
            'user_id' => null,
            'expires_at' => null,
        ]);

        expect($secondLink->id)->toBe($firstLink->id)
            ->and(Link::count())->toBe(1);
    });

    test('creates new link for different URL', function () {
        $action = app(CreateLink::class);

        $firstLink = $action->execute([
            'url' => 'https://example.com/page1',
            'user_id' => null,
            'expires_at' => null,
        ]);

        $secondLink = $action->execute([
            'url' => 'https://example.com/page2',
            'user_id' => null,
            'expires_at' => null,
        ]);

        expect($secondLink->id)->not->toBe($firstLink->id)
            ->and(Link::count())->toBe(2);
    });
});

describe('Validation', function () {
    test('rejects invalid URL', function () {
        $action = app(CreateLink::class);

        $action->execute([
            'url' => 'not-a-valid-url',
            'user_id' => null,
            'expires_at' => null,
        ]);
    })->throws(\InvalidArgumentException::class);

    test('rejects internal IP addresses', function () {
        $action = app(CreateLink::class);

        $action->execute([
            'url' => 'http://192.168.1.1/admin',
            'user_id' => null,
            'expires_at' => null,
        ]);
    })->throws(\InvalidArgumentException::class, 'Internal or private IP addresses are not allowed');

    test('rejects FTP URLs', function () {
        $action = app(CreateLink::class);

        $action->execute([
            'url' => 'ftp://example.com/file.txt',
            'user_id' => null,
            'expires_at' => null,
        ]);
    })->throws(\InvalidArgumentException::class, 'Only HTTP and HTTPS URLs are allowed');
});
