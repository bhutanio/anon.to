<?php

declare(strict_types=1);

use App\Actions\Links\CheckDuplicate;
use App\Models\Link;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)->group('feature');

beforeEach(function () {
    $this->action = app(CheckDuplicate::class);
});

test('returns null when URL does not exist', function () {
    $url = 'https://example.com/unique-url';

    $result = $this->action->execute($url);

    expect($result)->toBeNull();
});

test('returns existing link when URL exists', function () {
    $url = 'https://example.com/test-page';
    $urlHash = hash('sha256', $url);

    $existingLink = Link::factory()->create([
        'full_url' => $url,
        'full_url_hash' => $urlHash,
        'is_active' => true,
    ]);

    $result = $this->action->execute($url);

    expect($result)->not->toBeNull()
        ->id->toBe($existingLink->id)
        ->full_url->toBe($url);
});

test('returns null when link exists but is inactive', function () {
    $url = 'https://example.com/inactive-link';
    $urlHash = hash('sha256', $url);

    Link::factory()->create([
        'full_url' => $url,
        'full_url_hash' => $urlHash,
        'is_active' => false,
    ]);

    $result = $this->action->execute($url);

    expect($result)->toBeNull();
});

test('uses SHA256 hash for efficient lookup', function () {
    $url = 'https://example.com/very-long-url-with-lots-of-parameters?param1=value1&param2=value2&param3=value3';
    $urlHash = hash('sha256', $url);

    $existingLink = Link::factory()->create([
        'full_url' => $url,
        'full_url_hash' => $urlHash,
        'is_active' => true,
    ]);

    $result = $this->action->execute($url);

    expect($result)->not->toBeNull()
        ->id->toBe($existingLink->id);
});

test('differentiates between similar URLs', function () {
    $url1 = 'https://example.com/page1';
    $url2 = 'https://example.com/page2';

    Link::factory()->create([
        'full_url' => $url1,
        'full_url_hash' => hash('sha256', $url1),
        'is_active' => true,
    ]);

    $result = $this->action->execute($url2);

    expect($result)->toBeNull();
});
