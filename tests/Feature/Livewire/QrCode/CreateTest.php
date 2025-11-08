<?php

declare(strict_types=1);

use App\Livewire\QrCode\Create;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

beforeEach(function () {
    RateLimiter::clear('generate-qr:ip:'.hash('sha256', request()->ip()));
});

test('can generate QR code with valid content', function () {
    Livewire::test(Create::class)
        ->set('content', 'https://example.com')
        ->call('generateQrCode')
        ->assertSet('errorMessage', null)
        ->assertSet('content', '') // Content cleared after generation
        ->assertHasNoErrors();
});

test('enforces rate limit for anonymous users', function () {
    $component = Livewire::test(Create::class);

    // Generate 10 QR codes (the limit)
    for ($i = 0; $i < 10; $i++) {
        $component
            ->set('content', "Test content {$i}")
            ->call('generateQrCode')
            ->assertSet('errorMessage', null);
    }

    // 11th attempt should be rate limited
    $component
        ->set('content', 'Test content 11')
        ->call('generateQrCode')
        ->assertSet('errorMessage', fn ($message) => str_contains($message, 'Too many QR codes generated'));
});

test('enforces higher rate limit for authenticated users', function () {
    $user = User::factory()->create();
    RateLimiter::clear('generate-qr:user:'.$user->id);

    $component = Livewire::actingAs($user)->test(Create::class);

    // Generate 50 QR codes (the authenticated user limit)
    for ($i = 0; $i < 50; $i++) {
        $component
            ->set('content', "Test content {$i}")
            ->call('generateQrCode');
    }

    // 51st attempt should be rate limited
    $component
        ->set('content', 'Test content 51')
        ->call('generateQrCode')
        ->assertSet('errorMessage', fn ($message) => str_contains($message, 'Too many QR codes generated'));
});

test('validates content is required', function () {
    Livewire::test(Create::class)
        ->set('content', '')
        ->call('generateQrCode')
        ->assertHasErrors(['content' => 'required']);
});

test('validates content does not exceed 2900 characters', function () {
    $longContent = str_repeat('a', 2901);

    Livewire::test(Create::class)
        ->set('content', $longContent)
        ->call('generateQrCode')
        ->assertHasErrors(['content' => 'max']);
});

test('download PNG triggers successfully', function () {
    $response = Livewire::test(Create::class)
        ->set('content', 'https://example.com')
        ->call('downloadPng')
        ->assertOk();

    expect($response)->not->toBeNull();
});

test('download SVG triggers successfully', function () {
    $response = Livewire::test(Create::class)
        ->set('content', 'https://example.com')
        ->call('downloadSvg')
        ->assertOk();

    expect($response)->not->toBeNull();
});

test('download PDF triggers successfully', function () {
    $response = Livewire::test(Create::class)
        ->set('content', 'https://example.com')
        ->call('downloadPdf')
        ->assertOk();

    expect($response)->not->toBeNull();
});
