<?php

declare(strict_types=1);

use App\Actions\Links\CreateLink;
use App\Models\Link;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class)->group('feature');

beforeEach(function () {
    $this->action = app(CreateLink::class);
});

test('creates link with valid URL', function () {
    $data = [
        'url' => 'https://example.com/test-page',
        'user_id' => null,
    ];

    $link = $this->action->execute($data);

    expect($link)->toBeInstanceOf(Link::class)
        ->url_scheme->toBe('https')
        ->url_host->toBe('example.com')
        ->url_path->toBe('/test-page')
        ->full_url->toBe('https://example.com/test-page')
        ->hash->toHaveLength(6)
        ->is_active->toBeTrue()
        ->visits->toBe(0);
});

test('creates link with user ID', function () {
    $user = \App\Models\User::factory()->create();

    $data = [
        'url' => 'https://example.com/user-link',
        'user_id' => $user->id,
    ];

    $link = $this->action->execute($data);

    expect($link->user_id)->toBe($user->id);
});

test('parses URL into components correctly', function () {
    $data = [
        'url' => 'https://example.com:8080/path?query=value#fragment',
        'user_id' => null,
    ];

    $link = $this->action->execute($data);

    expect($link->url_scheme)->toBe('https')
        ->and($link->url_host)->toBe('example.com')
        ->and($link->url_port)->toBe(8080)
        ->and($link->url_path)->toBe('/path')
        ->and($link->url_query)->toBe('query=value')
        ->and($link->url_fragment)->toBe('fragment');
});

test('stores SHA256 hash of full URL', function () {
    $url = 'https://example.com/test';
    $expectedHash = hash('sha256', $url);

    $data = [
        'url' => $url,
        'user_id' => null,
    ];

    $link = $this->action->execute($data);

    expect($link->full_url_hash)->toBe($expectedHash);
});

test('caches link after creation', function () {
    $data = [
        'url' => 'https://example.com/cached',
        'user_id' => null,
    ];

    $link = $this->action->execute($data);

    $cached = Cache::get("link:{$link->hash}");

    expect($cached)->not->toBeNull()
        ->id->toBe($link->id);
});

test('returns existing link for duplicate URL', function () {
    $url = 'https://example.com/duplicate';

    // Create first link
    $firstLink = $this->action->execute([
        'url' => $url,
        'user_id' => null,
    ]);

    // Try to create duplicate
    $secondLink = $this->action->execute([
        'url' => $url,
        'user_id' => null,
    ]);

    expect($secondLink->id)->toBe($firstLink->id)
        ->and(Link::count())->toBe(1);
});

test('throws exception for invalid URL', function () {
    $data = [
        'url' => 'not-a-valid-url',
        'user_id' => null,
    ];

    $this->action->execute($data);
})->throws(\InvalidArgumentException::class);

test('throws exception for internal URL', function () {
    $data = [
        'url' => 'http://localhost/admin',
        'user_id' => null,
    ];

    $this->action->execute($data);
})->throws(\InvalidArgumentException::class, 'Internal or private IP addresses are not allowed');

test('throws exception when trying to shorten app URL', function () {
    // Uses APP_URL from phpunit.xml (http://anon.to.test)
    $appUrl = config('app.url');
    $parsed = parse_url($appUrl);
    $host = $parsed['host'] ?? 'localhost';

    $data = [
        'url' => "http://{$host}/abc123",
        'user_id' => null,
    ];

    $this->action->execute($data);
})->throws(\InvalidArgumentException::class, 'You cannot shorten a URL from this application');

test('throws exception when trying to shorten app URL with different path', function () {
    // Uses APP_URL from phpunit.xml (http://anon.to.test)
    $appUrl = config('app.url');
    $parsed = parse_url($appUrl);
    $host = $parsed['host'] ?? 'localhost';

    $data = [
        'url' => "https://{$host}/xyz789",
        'user_id' => null,
    ];

    $this->action->execute($data);
})->throws(\InvalidArgumentException::class, 'You cannot shorten a URL from this application');
