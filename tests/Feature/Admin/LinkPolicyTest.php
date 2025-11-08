<?php

declare(strict_types=1);

use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Link Policy Admin Overrides', function () {
    test('admin can force view any link', function () {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);
        $link = Link::factory()->create([
            'user_id' => $user->id,
            'is_active' => false,
        ]);

        $this->actingAs($admin);

        expect($admin->can('forceView', $link))->toBeTrue();
    });

    test('non-admin cannot force view links', function () {
        $user = User::factory()->create(['is_admin' => false]);
        $otherUser = User::factory()->create(['is_admin' => false]);
        $link = Link::factory()->create([
            'user_id' => $otherUser->id,
            'is_active' => false,
        ]);

        $this->actingAs($user);

        expect($user->can('forceView', $link))->toBeFalse();
    });

    test('admin can delete any link', function () {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);
        $link = Link::factory()->create(['user_id' => $user->id]);

        $this->actingAs($admin);

        expect($admin->can('adminDelete', $link))->toBeTrue();
    });

    test('non-admin cannot admin delete links', function () {
        $user = User::factory()->create(['is_admin' => false]);
        $otherUser = User::factory()->create(['is_admin' => false]);
        $link = Link::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($user);

        expect($user->can('adminDelete', $link))->toBeFalse();
    });
});
