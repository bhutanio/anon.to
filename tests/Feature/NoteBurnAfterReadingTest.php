<?php

declare(strict_types=1);

use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

test('note is deleted after reaching view limit', function () {
    $note = Note::factory()->create([
        'content' => 'Burn after reading',
        'view_limit' => 1,
        'views' => 0,
        'is_active' => true,
    ]);

    // First view should delete the note
    $response = $this->get("/n/{$note->hash}");

    // Verify note was deleted
    expect(Note::find($note->id))->toBeNull();
});

test('note shows warning when approaching view limit', function () {
    $note = Note::factory()->create([
        'content' => 'Limited views',
        'view_limit' => 5,
        'views' => 3,
        'is_active' => true,
    ]);

    $response = $this->get("/n/{$note->hash}");

    // Check for both possible formats: "1 view remaining" or "views remaining"
    $response->assertSee('view');
    $response->assertSee('remaining');
});

test('view counter increments on each access', function () {
    $note = Note::factory()->create([
        'content' => 'Test content',
        'views' => 0,
        'is_active' => true,
    ]);

    $this->get("/n/{$note->hash}");

    $note->refresh();
    expect($note->views)->toBe(1);

    $this->get("/n/{$note->hash}");

    $note->refresh();
    expect($note->views)->toBe(2);
});

test('cache is cleared when note is deleted after view limit', function () {
    $note = Note::factory()->create([
        'content' => 'Burn after reading',
        'view_limit' => 1,
        'views' => 0,
        'is_active' => true,
    ]);

    // Cache the note
    Cache::put("note:{$note->hash}", $note, 86400);

    // Access the note
    $this->get("/n/{$note->hash}");

    // Verify cache was cleared
    expect(Cache::has("note:{$note->hash}"))->toBeFalse();
});
