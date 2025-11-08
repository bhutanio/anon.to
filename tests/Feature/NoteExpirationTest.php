<?php

declare(strict_types=1);

use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

test('expired note returns 410 Gone when accessed', function () {
    $note = Note::factory()->expired()->create([
        'content' => 'Expired content',
        'is_active' => true,
    ]);

    $response = $this->get("/n/{$note->hash}");

    $response->assertStatus(410)
        ->assertSee('Note Expired')
        ->assertDontSee('Expired content');
});

test('non-expired note returns 200 OK', function () {
    $note = Note::factory()->create([
        'content' => 'Active content',
        'expires_at' => now()->addDays(7),
        'is_active' => true,
    ]);

    $response = $this->get("/n/{$note->hash}");

    $response->assertOk();
});

test('note with no expiration date is accessible', function () {
    $note = Note::factory()->create([
        'content' => 'Never expires',
        'expires_at' => null,
        'is_active' => true,
    ]);

    $response = $this->get("/n/{$note->hash}");

    $response->assertOk();
});

test('scheduled command deletes expired notes', function () {
    // Create expired notes
    $expiredNote1 = Note::factory()->create([
        'expires_at' => now()->subDay(),
    ]);

    $expiredNote2 = Note::factory()->create([
        'expires_at' => now()->subHour(),
    ]);

    // Create non-expired note
    $activeNote = Note::factory()->create([
        'expires_at' => now()->addDay(),
    ]);

    // Run the command
    $this->artisan('notes:delete-expired')
        ->assertExitCode(0);

    // Verify expired notes were deleted
    expect(Note::find($expiredNote1->id))->toBeNull();
    expect(Note::find($expiredNote2->id))->toBeNull();

    // Verify active note still exists
    expect(Note::find($activeNote->id))->not->toBeNull();
});

test('scheduled command clears cache for deleted notes', function () {
    $expiredNote = Note::factory()->create([
        'expires_at' => now()->subDay(),
    ]);

    // Cache the note
    Cache::put("note:{$expiredNote->hash}", $expiredNote, 86400);

    // Verify cache exists
    expect(Cache::has("note:{$expiredNote->hash}"))->toBeTrue();

    // Run the command
    $this->artisan('notes:delete-expired');

    // Verify cache was cleared
    expect(Cache::has("note:{$expiredNote->hash}"))->toBeFalse();
});
