<?php

use App\Livewire\Dashboard\Index;
use App\Models\Note;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

test('authenticated user can view their notes on dashboard', function () {
    $user = User::factory()->create();
    $notes = Note::factory()->count(3)->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test(Index::class)
        ->call('switchTab', 'notes')
        ->assertSee($notes->first()->hash);
});

test('dashboard shows empty state when user has no notes', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Index::class)
        ->call('switchTab', 'notes')
        ->assertSee('No notes yet');
});

test('user can delete their own note from dashboard', function () {
    $user = User::factory()->create();
    $note = Note::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Cache::put("note:{$note->hash}", $note, 86400);

    Livewire::test(Index::class)
        ->call('deleteNote', $note->id);

    expect(Note::find($note->id))->toBeNull();
    expect(Cache::has("note:{$note->hash}"))->toBeFalse();
});

test('dashboard notes are ordered by creation date descending', function () {
    $user = User::factory()->create();

    $oldNote = Note::factory()->create([
        'user_id' => $user->id,
        'created_at' => now()->subDays(5),
    ]);

    $newNote = Note::factory()->create([
        'user_id' => $user->id,
        'created_at' => now(),
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Index::class);
    $notes = $component->get('notes');

    expect($notes->first()->id)->toBe($newNote->id);
    expect($notes->last()->id)->toBe($oldNote->id);
});

test('user cannot see other users notes on dashboard', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $user1Note = Note::factory()->create(['user_id' => $user1->id]);
    $user2Note = Note::factory()->create(['user_id' => $user2->id]);

    $this->actingAs($user1);

    $component = Livewire::test(Index::class);
    $notes = $component->get('notes');

    expect($notes)->toHaveCount(1);
    expect($notes->first()->id)->toBe($user1Note->id);
    expect($notes->contains('id', $user2Note->id))->toBeFalse();
});
