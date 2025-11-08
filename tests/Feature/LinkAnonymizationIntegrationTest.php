<?php

declare(strict_types=1);

use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class)->group('feature', 'integration');

describe('Link Anonymization Integration Tests', function () {
    test('complete workflow: create link, visit warning page, and continue to destination', function () {
        // Create a saved link
        $link = Link::factory()
            ->withUrl('https://example.com/complete-workflow')
            ->create();

        // Visit the warning page
        $response = get("/{$link->hash}");

        $response->assertOk()
            ->assertSee('example.com', false)
            ->assertSee('Continue to Site', false)
            ->assertSee('in the future', false); // Domain trust checkbox

        // Verify domain trust checkbox is present
        $response->assertSee('x-model="trustDomain"', false);
    });

    test('complete workflow: direct URL anonymization displays warning without database record', function () {
        // Count links before
        $linksBefore = Link::count();

        // Visit homepage with URL parameter
        $response = get('/?url=https://github.com/laravel');

        $response->assertOk()
            ->assertSee('github.com', false)
            ->assertSee('Continue to Site', false);

        // Verify no database record was created
        expect(Link::count())->toBe($linksBefore);
    });

    test('navigation appears consistently across guest pages', function () {
        // Homepage
        $homeResponse = get('/');
        $homeResponse->assertOk()
            ->assertSee('anon.to', false)
            ->assertSee('Sign In', false)
            ->assertSee('Sign Up', false)
            ->assertSee('Light', false)
            ->assertSee('Dark', false);

        // Redirect warning page (direct anonymization)
        $warningResponse = get('/?url=https://example.com');
        $warningResponse->assertOk()
            ->assertSee('anon.to', false)
            ->assertSee('Sign In', false)
            ->assertSee('Sign Up', false);

        // Redirect warning page (saved link)
        $link = Link::factory()
            ->withUrl('https://test.com')
            ->create();
        $linkResponse = get("/{$link->hash}");
        $linkResponse->assertOk()
            ->assertSee('anon.to', false)
            ->assertSee('Sign In', false)
            ->assertSee('Sign Up', false);
    });

    test('navigation shows correct links for authenticated users', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->get('/dashboard');

        $response->assertOk()
            ->assertSee('Dashboard', false)
            ->assertDontSee('Sign In', false)
            ->assertDontSee('Sign Up', false);
    });

    test('domain trust checkbox appears on both saved links and direct anonymization', function () {
        // Saved link
        $savedLink = Link::factory()
            ->withUrl('https://saved.example.com')
            ->create();
        $savedResponse = get("/{$savedLink->hash}");
        $savedResponse->assertOk()
            ->assertSee('saved.example.com', false)
            ->assertSee('in the future', false)
            ->assertSee('x-model="trustDomain"', false);

        // Direct anonymization
        $directResponse = get('/?url=https://direct.example.com');
        $directResponse->assertOk()
            ->assertSee('direct.example.com', false)
            ->assertSee('in the future', false)
            ->assertSee('x-model="trustDomain"', false);
    });

    test('different domains show different checkbox labels', function () {
        $response1 = get('/?url=https://github.com/laravel');
        $response1->assertOk()
            ->assertSee('github.com', false)
            ->assertDontSee('blog.github.com', false);

        $response2 = get('/?url=https://example.com/test');
        $response2->assertOk()
            ->assertSee('example.com', false)
            ->assertDontSee('github.com', false);
    });

    test('redirect warning shows metadata for saved links but not direct anonymization', function () {
        // Saved link - should show metadata
        $link = Link::factory()
            ->withUrl('https://example.com/saved')
            ->create();

        // Force some visits
        $link->update(['visits' => 5]);

        $savedResponse = get("/{$link->hash}");
        $savedResponse->assertOk();

        // Get the HTML content
        $html = $savedResponse->getContent();

        // Should contain visits count (allowing for formatting like "5" or "5,000")
        expect($html)->toContain('visit');

        // Direct anonymization - should NOT show metadata
        $directResponse = get('/?url=https://example.com/direct');
        $directHtml = $directResponse->getContent();

        // Should NOT contain visit-related text
        expect($directHtml)->not->toMatch('/\d+\s+visit/i');
    });

    test('navigation renders placeholder features with coming soon tooltip', function () {
        $response = get('/');

        $response->assertOk()
            ->assertSee('QR Code', false)
            ->assertSee('Notes', false)
            ->assertSee('Coming Soon', false);
    });

    test('invalid url parameter shows error while maintaining navigation', function () {
        $response = get('/?url=invalid-url-format');

        $response->assertOk()
            ->assertSee('Invalid URL', false)
            // Navigation should still be present
            ->assertSee('anon.to', false)
            ->assertSee('Sign In', false);
    });

    test('multiple url components are correctly displayed in warning page', function () {
        $complexUrl = 'https://user:pass@example.com:8080/path/to/resource?foo=bar&baz=qux#section';
        $response = get('/?url='.urlencode($complexUrl));

        $response->assertOk()
            ->assertSee('example.com', false)
            ->assertSee('8080', false)
            ->assertSee('/path/to/resource', false)
            ->assertSee('foo=bar', false)
            ->assertSee('section', false);
    });
});
