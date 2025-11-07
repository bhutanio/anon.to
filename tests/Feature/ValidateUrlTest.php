<?php

declare(strict_types=1);

use App\Actions\Links\ValidateUrl;

uses()->group('feature');

beforeEach(function () {
    $this->action = app(ValidateUrl::class);
});

test('validates URL successfully', function () {
    $url = 'https://example.com/path';

    expect(fn () => $this->action->execute($url))->not->toThrow(\InvalidArgumentException::class);
});

test('rejects empty URL', function () {
    $this->action->execute('');
})->throws(\InvalidArgumentException::class, 'URL cannot be empty');

test('rejects URL with only whitespace', function () {
    $this->action->execute('   ');
})->throws(\InvalidArgumentException::class, 'URL cannot be empty');

test('rejects URL exceeding max length', function () {
    config(['anon.max_url_length' => 100]);

    $longUrl = 'https://example.com/'.str_repeat('a', 200);

    $this->action->execute($longUrl);
})->throws(\InvalidArgumentException::class, 'URL cannot exceed 100 characters');

test('rejects invalid URL format', function () {
    $this->action->execute('not a valid url');
})->throws(\InvalidArgumentException::class, 'Invalid URL format');

test('rejects URL without scheme', function () {
    $this->action->execute('example.com/path');
})->throws(\InvalidArgumentException::class);

test('rejects FTP URLs', function () {
    $this->action->execute('ftp://example.com/file.txt');
})->throws(\InvalidArgumentException::class, 'Only HTTP and HTTPS URLs are allowed');

test('rejects file URLs', function () {
    $this->action->execute('file:///etc/passwd');
})->throws(\InvalidArgumentException::class); // Message varies - could be "Invalid URL format" or scheme error

test('accepts HTTP URLs', function () {
    expect(fn () => $this->action->execute('http://example.com'))->not->toThrow(\InvalidArgumentException::class);
});

test('accepts HTTPS URLs', function () {
    expect(fn () => $this->action->execute('https://example.com'))->not->toThrow(\InvalidArgumentException::class);
});

test('rejects localhost URL', function () {
    $this->action->execute('http://localhost/path');
})->throws(\InvalidArgumentException::class, 'Internal or private IP addresses are not allowed');

test('rejects 127.0.0.1 URL', function () {
    $this->action->execute('http://127.0.0.1/path');
})->throws(\InvalidArgumentException::class, 'Internal or private IP addresses are not allowed');

test('rejects 10.x.x.x private range', function () {
    $this->action->execute('http://10.0.0.1/path');
})->throws(\InvalidArgumentException::class, 'Internal or private IP addresses are not allowed');

test('rejects 192.168.x.x private range', function () {
    $this->action->execute('http://192.168.1.1/path');
})->throws(\InvalidArgumentException::class, 'Internal or private IP addresses are not allowed');

test('rejects 172.16-31.x.x private range', function () {
    $this->action->execute('http://172.16.0.1/path');
})->throws(\InvalidArgumentException::class, 'Internal or private IP addresses are not allowed');

test('accepts public domain names', function () {
    expect(fn () => $this->action->execute('https://google.com'))->not->toThrow(\InvalidArgumentException::class);
    expect(fn () => $this->action->execute('https://github.com/path'))->not->toThrow(\InvalidArgumentException::class);
});
