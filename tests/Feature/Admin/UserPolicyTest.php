<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('User Policy', function () {
    test('admin can view users list', function () {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin);

        expect($admin->can('viewAny', User::class))->toBeTrue();
    });

    test('non-admin cannot view users list', function () {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user);

        expect($user->can('viewAny', User::class))->toBeFalse();
    });

    test('admin can ban non-admin users', function () {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($admin);

        expect($admin->can('ban', $user))->toBeTrue();
    });

    test('admin cannot ban themselves', function () {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin);

        expect($admin->can('ban', $admin))->toBeFalse();
    });

    test('admin cannot ban other admins', function () {
        $admin1 = User::factory()->create(['is_admin' => true]);
        $admin2 = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin1);

        expect($admin1->can('ban', $admin2))->toBeFalse();
    });

    test('admin can verify users', function () {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($admin);

        expect($admin->can('verify', $user))->toBeTrue();
    });

    test('admin can promote users to admin', function () {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($admin);

        expect($admin->can('promote', $user))->toBeTrue();
    });

    test('admin cannot promote themselves', function () {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin);

        expect($admin->can('promote', $admin))->toBeFalse();
    });

    test('non-admin cannot ban users', function () {
        $user1 = User::factory()->create(['is_admin' => false]);
        $user2 = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user1);

        expect($user1->can('ban', $user2))->toBeFalse();
    });

    test('non-admin cannot verify users', function () {
        $user1 = User::factory()->create(['is_admin' => false]);
        $user2 = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user1);

        expect($user1->can('verify', $user2))->toBeFalse();
    });

    test('non-admin cannot promote users', function () {
        $user1 = User::factory()->create(['is_admin' => false]);
        $user2 = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user1);

        expect($user1->can('promote', $user2))->toBeFalse();
    });
});
