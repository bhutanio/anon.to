<?php

declare(strict_types=1);

use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

describe('Note Policy Admin Overrides', function () {
    test('admin can force view any note', function () {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);
        $note = Note::factory()->create([
            'user_id' => $user->id,
            'is_active' => false,
        ]);

        $this->actingAs($admin);

        expect($admin->can('forceView', $note))->toBeTrue();
    });

    test('admin can force view expired notes', function () {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);
        $note = Note::factory()->expired()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($admin);

        expect($admin->can('forceView', $note))->toBeTrue();
    });

    test('admin can force view password protected notes', function () {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);
        $note = Note::factory()->create([
            'user_id' => $user->id,
            'password_hash' => Hash::make('secret'),
        ]);

        $this->actingAs($admin);

        expect($admin->can('forceView', $note))->toBeTrue();
    });

    test('non-admin cannot force view notes', function () {
        $user = User::factory()->create(['is_admin' => false]);
        $otherUser = User::factory()->create(['is_admin' => false]);
        $note = Note::factory()->create([
            'user_id' => $otherUser->id,
            'is_active' => false,
        ]);

        $this->actingAs($user);

        expect($user->can('forceView', $note))->toBeFalse();
    });

    test('admin can delete any note', function () {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);
        $note = Note::factory()->create(['user_id' => $user->id]);

        $this->actingAs($admin);

        expect($admin->can('adminDelete', $note))->toBeTrue();
    });

    test('non-admin cannot admin delete notes', function () {
        $user = User::factory()->create(['is_admin' => false]);
        $otherUser = User::factory()->create(['is_admin' => false]);
        $note = Note::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($user);

        expect($user->can('adminDelete', $note))->toBeFalse();
    });
});
