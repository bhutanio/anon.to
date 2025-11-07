<?php

declare(strict_types=1);

use App\Livewire\Home;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Clear all rate limiters before each test
    RateLimiter::clear('create-link:ip:127.0.0.1');

    // Also clear user-specific rate limiters (in case tests create users)
    for ($i = 1; $i <= 10; $i++) {
        RateLimiter::clear("create-link:user:{$i}");
    }
});

describe('Anonymous User Rate Limiting', function () {
    test('anonymous users are limited to 20 requests per hour', function () {
        // Make 20 requests (should all succeed)
        for ($i = 0; $i < 20; $i++) {
            Livewire::test(Home::class)
                ->set('url', "https://example.com/page{$i}")
                ->call('createLink')
                ->assertSet('errorMessage', null);
        }

        // 21st request should be rate limited
        Livewire::test(Home::class)
            ->set('url', 'https://example.com/page21')
            ->call('createLink')
            ->assertSet('errorMessage', fn ($message) => str_contains($message, 'Too many requests'));
    });

    test('rate limit is per IP address', function () {
        // Make 20 requests from first IP
        for ($i = 0; $i < 20; $i++) {
            Livewire::test(Home::class)
                ->set('url', "https://example.com/ip1-page{$i}")
                ->call('createLink');
        }

        // 21st request from same IP should be limited
        Livewire::test(Home::class)
            ->set('url', 'https://example.com/ip1-page21')
            ->call('createLink')
            ->assertSet('errorMessage', fn ($message) => str_contains($message, 'Too many requests'));
    });

    test('returns helpful error message when rate limited', function () {
        // Exhaust the limit
        for ($i = 0; $i < 20; $i++) {
            Livewire::test(Home::class)
                ->set('url', "https://example.com/limit-test{$i}")
                ->call('createLink');
        }

        // Next request should be rate limited with message
        Livewire::test(Home::class)
            ->set('url', 'https://example.com/limit-test21')
            ->call('createLink')
            ->assertSet('errorMessage', fn ($message) => str_contains($message, 'Too many requests'));
    });
});

describe('Registered User Rate Limiting', function () {
    test('registered users are limited to 100 requests per hour', function () {
        $user = User::factory()->create();

        // Make 100 requests (should all succeed)
        for ($i = 0; $i < 100; $i++) {
            Livewire::actingAs($user)
                ->test(Home::class)
                ->set('url', "https://example.com/user-page{$i}")
                ->call('createLink')
                ->assertSet('errorMessage', null);
        }

        // 101st request should be rate limited
        Livewire::actingAs($user)
            ->test(Home::class)
            ->set('url', 'https://example.com/user-page101')
            ->call('createLink')
            ->assertSet('errorMessage', fn ($message) => str_contains($message, 'Too many requests'));
    });

    test('rate limit is per user ID', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Make 100 requests from first user
        for ($i = 0; $i < 100; $i++) {
            Livewire::actingAs($user1)
                ->test(Home::class)
                ->set('url', "https://example.com/user1-page{$i}")
                ->call('createLink');
        }

        // 101st request from user1 should be limited
        Livewire::actingAs($user1)
            ->test(Home::class)
            ->set('url', 'https://example.com/user1-page101')
            ->call('createLink')
            ->assertSet('errorMessage', fn ($message) => str_contains($message, 'Too many requests'));

        // But request from user2 should work
        Livewire::actingAs($user2)
            ->test(Home::class)
            ->set('url', 'https://example.com/user2-page1')
            ->call('createLink')
            ->assertSet('errorMessage', null);
    });

    test('registered users get higher limit than anonymous users', function () {
        $user = User::factory()->create();

        // Anonymous user should be limited at 20
        for ($i = 0; $i < 21; $i++) {
            $test = Livewire::test(Home::class)
                ->set('url', "https://example.com/anon{$i}")
                ->call('createLink');

            if ($i < 20) {
                $test->assertSet('errorMessage', null);
            } else {
                $test->assertSet('errorMessage', fn ($message) => str_contains($message, 'Too many requests'));
            }
        }

        // Clear rate limiter for both anonymous and authenticated users
        RateLimiter::clear('create-link:ip:127.0.0.1');
        RateLimiter::clear("create-link:user:{$user->id}");

        // Registered user should be able to make more than 20
        for ($i = 0; $i < 50; $i++) {
            Livewire::actingAs($user)
                ->test(Home::class)
                ->set('url', "https://example.com/auth{$i}")
                ->call('createLink')
                ->assertSet('errorMessage', null);
        }
    });

    test('returns helpful error message for registered users', function () {
        $user = User::factory()->create();

        // Exhaust the limit
        for ($i = 0; $i < 100; $i++) {
            Livewire::actingAs($user)
                ->test(Home::class)
                ->set('url', "https://example.com/limit{$i}")
                ->call('createLink');
        }

        // Next request should be rate limited
        Livewire::actingAs($user)
            ->test(Home::class)
            ->set('url', 'https://example.com/limit101')
            ->call('createLink')
            ->assertSet('errorMessage', fn ($message) => str_contains($message, 'Too many requests'));
    });
});
