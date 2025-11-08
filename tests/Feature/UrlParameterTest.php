<?php

declare(strict_types=1);

use function Pest\Laravel\get;

describe('URL parameter handling', function () {
    test('homepage accepts url parameter in standard format', function () {
        $response = get('/?url=https://example.com');

        $response->assertOk()
            ->assertSee('example.com', false)
            ->assertSee('Continue to Site', false);
    });

    test('homepage accepts url parameter without url= prefix', function () {
        $response = get('/?https://example.com');

        $response->assertOk()
            ->assertSee('example.com', false)
            ->assertSee('Continue to Site', false);
    });

    test('validates url parameter using ValidateUrl action', function () {
        $response = get('/?url=https://example.com');

        $response->assertOk()
            ->assertSee('example.com', false)
            ->assertSee('Continue to Site', false);
    });

    test('rejects invalid url formats', function () {
        $response = get('/?url=not-a-valid-url');

        $response->assertOk()
            ->assertSee('Invalid URL', false);
    });

    test('handles malformed parameters gracefully', function () {
        $response = get('/?url=javascript:alert("xss")');

        $response->assertOk()
            ->assertSee('Invalid URL', false);
    });

    test('url parsing extracts components correctly', function () {
        $response = get('/?url=https://example.com:8080/path?query=value#fragment');

        $response->assertOk()
            ->assertSee('example.com', false)
            ->assertSee('8080', false)
            ->assertSee('/path', false)
            ->assertSee('query=value', false)
            ->assertSee('fragment', false);
    });

    test('prevents ssrf attacks with internal urls', function () {
        $response = get('/?url=http://127.0.0.1');

        $response->assertOk()
            ->assertSee('Invalid URL', false);
    });

    test('prevents ssrf attacks with localhost', function () {
        $response = get('/?url=http://localhost/admin');

        $response->assertOk()
            ->assertSee('Invalid URL', false);
    });

    test('displays warning page without visit count for direct anonymization', function () {
        $response = get('/?url=https://example.com/test');

        $response->assertOk()
            ->assertSee('example.com', false)
            ->assertDontSee('Visit count', false)
            ->assertDontSee('Created', false);
    });
});
