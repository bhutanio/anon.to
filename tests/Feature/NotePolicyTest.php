<?php

declare(strict_types=1);

use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('owner can delete their own note', function () {
    $user = User::factory()->create();
    $note = Note::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    $response = $user->can('delete', $note);

    expect($response)->toBeTrue();
});

test('user cannot delete another users note', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $note = Note::factory()->create(['user_id' => $user1->id]);

    $this->actingAs($user2);

    $response = $user2->can('delete', $note);

    expect($response)->toBeFalse();
});

test('guest cannot delete any note via dashboard', function () {
    $user = User::factory()->create();
    $note = Note::factory()->create(['user_id' => $user->id]);

    // Try to access dashboard without authentication
    $response = $this->get('/dashboard');

    // Should redirect to login
    $response->assertRedirect('/login');
});

test('user cannot update note (immutable)', function () {
    $user = User::factory()->create();
    $note = Note::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    $response = $user->can('update', $note);

    expect($response)->toBeFalse();
});

test('anyone can view active non-expired note', function () {
    $note = Note::factory()->create([
        'is_active' => true,
        'expires_at' => now()->addDay(),
    ]);

    $response = $this->get("/n/{$note->hash}");

    $response->assertOk();
});

test('owner can view their expired note in dashboard', function () {
    $user = User::factory()->create();
    $note = Note::factory()->expired()->create([
        'user_id' => $user->id,
        'is_active' => true,
    ]);

    $this->actingAs($user);

    $response = $user->can('view', $note);

    expect($response)->toBeTrue();
});
