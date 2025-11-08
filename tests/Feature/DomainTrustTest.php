<?php

declare(strict_types=1);

use App\Models\Link;

use function Pest\Laravel\get;

test('redirect warning page displays checkbox with correct domain name', function () {
    $link = Link::factory()
        ->withUrl('https://example.com/test')
        ->create();

    $response = get("/{$link->hash}");

    $response->assertOk()
        ->assertSee('example.com', false)
        ->assertSee('in the future', false)
        ->assertSee('type="checkbox"', false)
        ->assertSee('x-model="trustDomain"', false);
});

test('redirect warning page displays checkbox for direct URL anonymization', function () {
    $response = get('/?url=https://github.com/laravel');

    $response->assertOk()
        ->assertSee('github.com', false)
        ->assertSee('in the future', false)
        ->assertSee('type="checkbox"', false)
        ->assertSee('x-model="trustDomain"', false);
});

test('checkbox label dynamically shows correct domain name for various URLs', function (string $url, string $expectedDomain) {
    $response = get("/?url={$url}");

    $response->assertOk()
        ->assertSee($expectedDomain, false)
        ->assertSee('x-model="trustDomain"', false);
})->with([
    ['https://example.com', 'example.com'],
    ['https://blog.example.com', 'blog.example.com'],
    ['https://github.com/user/repo', 'github.com'],
]);

test('redirect warning page includes Alpine.js data attributes for domain trust', function () {
    $link = Link::factory()
        ->withUrl('https://example.com/test')
        ->create();

    $response = get("/{$link->hash}");

    $response->assertOk()
        ->assertSee('x-data', false)
        ->assertSee('x-init', false)
        ->assertSee('trustDomain', false);
});

test('redirect warning page passes destination URL to Alpine.js', function () {
    $link = Link::factory()
        ->withUrl('https://example.com/test')
        ->create();

    $response = get("/{$link->hash}");

    $response->assertOk()
        ->assertSee('example.com', false);
});

test('redirect warning works for both saved links and direct anonymization', function () {
    // Saved link
    $link = Link::factory()
        ->withUrl('https://example.com/saved')
        ->create();
    $savedResponse = get("/{$link->hash}");
    $savedResponse->assertOk()
        ->assertSee('example.com', false)
        ->assertSee('in the future', false)
        ->assertSee('x-model="trustDomain"', false);

    // Direct anonymization
    $directResponse = get('/?url=https://example.com/direct');
    $directResponse->assertOk()
        ->assertSee('example.com', false)
        ->assertSee('in the future', false)
        ->assertSee('x-model="trustDomain"', false);
});

test('exact domain matching enforced in Alpine.js logic', function () {
    // This test verifies that the partial includes the correct domain
    // The actual localStorage matching will be tested in browser tests
    $response = get('/?url=https://blog.example.com/post');

    $response->assertOk()
        ->assertSee('blog.example.com', false)
        ->assertDontSee('warn me about example.com', false);
});

test('domain trust checkbox appears below security warning', function () {
    $response = get('/?url=http://insecure.com/test');

    $response->assertOk()
        ->assertSeeInOrder([
            'This link uses an insecure connection',
            'insecure.com',
            'in the future',
        ], false);
});
