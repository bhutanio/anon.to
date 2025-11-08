<?php

declare(strict_types=1);

use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    RateLimiter::clear('generate-qr:ip:'.hash('sha256', request()->ip()));
});

test('complete QR code generation user journey', function () {
    $page = visit('/qr');

    $page
        ->assertSee('Generate QR Codes')
        ->assertSee('Securely')
        ->assertSee('Privacy-first QR code generation')
        ->assertNoJavascriptErrors();

    // Fill in content
    $page
        ->fill('content', 'https://example.com/test-url')
        ->assertSee('/ 2,900 characters');

    // Click generate button
    $page
        ->click('Generate QR Code')
        ->waitFor('.rounded-lg.shadow-lg', 5); // Wait for QR code image to appear

    // Verify QR code preview is displayed
    $page
        ->assertSee('Your QR Code')
        ->assertSee('Download PNG')
        ->assertSee('Download SVG')
        ->assertSee('Download PDF');
});

test('character counter updates as user types', function () {
    $page = visit('/qr');

    $page
        ->fill('content', 'Test')
        ->assertSee('4 / 2,900 characters');

    $page
        ->fill('content', 'Test content with more characters')
        ->assertSee('/ 2,900 characters');
});

test('validates empty content', function () {
    $page = visit('/qr');

    $page
        ->click('Generate QR Code')
        ->waitFor('.text-red-600', 2)
        ->assertSee('Please enter content for your QR code');
});
