<?php

declare(strict_types=1);

use App\Actions\QrCode\GenerateQrCode;

test('generates PNG QR code with valid content', function () {
    $action = new GenerateQrCode;

    $result = $action->execute('https://example.com', 'png');

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);
    expect(substr($result, 0, 8))->toBe("\x89PNG\r\n\x1a\n");
});

test('generates SVG QR code with valid content', function () {
    $action = new GenerateQrCode;

    $result = $action->execute('https://example.com', 'svg');

    expect($result)->toBeString();
    expect($result)->toContain('<svg');
    expect($result)->toContain('</svg>');
});

test('generates PDF QR code with valid content', function () {
    $action = new GenerateQrCode;

    $result = $action->execute('https://example.com', 'pdf');

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);
    expect(substr($result, 0, 4))->toBe('%PDF');
});

test('throws exception for empty content', function () {
    $action = new GenerateQrCode;

    $action->execute('', 'png');
})->throws(\InvalidArgumentException::class, 'Content is required.');

test('throws exception for content exceeding 2900 characters', function () {
    $action = new GenerateQrCode;
    $longContent = str_repeat('a', 2901);

    $action->execute($longContent, 'png');
})->throws(\InvalidArgumentException::class, 'Content cannot exceed 2,900 characters.');

test('accepts content with large character count', function () {
    $action = new GenerateQrCode;
    // Use 1000 characters which is safely within QR code limits
    $largeContent = str_repeat('Test content with URL: https://example.com/path?query=value ', 20);

    $result = $action->execute($largeContent, 'png');

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);
});
