<?php

declare(strict_types=1);

use App\Actions\Notes\CreateNote;
use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class)->group('feature');

beforeEach(function () {
    $this->action = app(CreateNote::class);
});

test('creates note with valid content', function () {
    $data = [
        'content' => 'Hello World',
        'syntax' => 'plaintext',
        'title' => null,
        'password' => null,
        'expires_at' => null,
        'view_limit' => null,
        'user_id' => null,
    ];

    $note = $this->action->execute($data);

    expect($note)->toBeInstanceOf(Note::class)
        ->content->toBe('Hello World')
        ->syntax->toBe('plaintext')
        ->hash->toHaveLength(8)
        ->is_active->toBeTrue()
        ->views->toBe(0)
        ->char_count->toBe(11)
        ->line_count->toBe(1);
});

test('creates note with password and hashes it', function () {
    $data = [
        'content' => 'Secret content',
        'syntax' => 'plaintext',
        'title' => null,
        'password' => 'mysecretpass',
        'expires_at' => null,
        'view_limit' => null,
        'user_id' => null,
    ];

    $note = $this->action->execute($data);

    expect($note->password_hash)->not->toBeNull()
        ->and(Hash::check('mysecretpass', $note->password_hash))->toBeTrue();
});

test('creates note with all optional fields', function () {
    $user = User::factory()->create();
    $expiresAt = now()->addDays(7)->toDateTimeString();

    $data = [
        'content' => "<?php\necho 'Hello';\nreturn true;",
        'syntax' => 'php',
        'title' => 'My Test Note',
        'password' => 'testpass123',
        'expires_at' => $expiresAt,
        'view_limit' => 10,
        'user_id' => $user->id,
    ];

    $note = $this->action->execute($data);

    expect($note->title)->toBe('My Test Note');
    expect($note->syntax)->toBe('php');
    expect($note->user_id)->toBe($user->id);
    expect($note->view_limit)->toBe(10);
    expect($note->line_count)->toBe(3);
    expect($note->is_code)->toBeTrue();
    expect($note->expires_at)->not->toBeNull();
});

test('generates unique 8-character hash', function () {
    $data = [
        'content' => 'Test content',
        'syntax' => 'plaintext',
        'title' => null,
        'password' => null,
        'expires_at' => null,
        'view_limit' => null,
        'user_id' => null,
    ];

    $note1 = $this->action->execute($data);
    $note2 = $this->action->execute(['content' => 'Different content'] + $data);

    expect($note1->hash)->toHaveLength(8)
        ->and($note2->hash)->toHaveLength(8)
        ->and($note1->hash)->not->toBe($note2->hash);
});

test('stores SHA256 content hash', function () {
    $content = 'Test content for hashing';
    $expectedHash = hash('sha256', $content);

    $data = [
        'content' => $content,
        'syntax' => 'plaintext',
        'title' => null,
        'password' => null,
        'expires_at' => null,
        'view_limit' => null,
        'user_id' => null,
    ];

    $note = $this->action->execute($data);

    expect($note->content_hash)->toBe($expectedHash);
});

test('caches note after creation', function () {
    $data = [
        'content' => 'Cached content',
        'syntax' => 'plaintext',
        'title' => null,
        'password' => null,
        'expires_at' => null,
        'view_limit' => null,
        'user_id' => null,
    ];

    $note = $this->action->execute($data);

    $cached = Cache::get("note:{$note->hash}");

    expect($cached)->not->toBeNull()
        ->id->toBe($note->id);
});

test('returns existing note for duplicate content from same user', function () {
    $user = User::factory()->create();
    $content = 'Duplicate content test';

    $data = [
        'content' => $content,
        'syntax' => 'plaintext',
        'title' => null,
        'password' => null,
        'expires_at' => null,
        'view_limit' => null,
        'user_id' => null,
    ];

    // Create first note
    $firstNote = $this->action->execute($data);

    // Try to create duplicate
    $secondNote = $this->action->execute($data);

    expect($secondNote->id)->toBe($firstNote->id)
        ->and(Note::count())->toBe(1);
});

test('throws exception for empty content', function () {
    $data = [
        'content' => '',
        'syntax' => 'plaintext',
        'title' => null,
        'password' => null,
        'expires_at' => null,
        'view_limit' => null,
        'user_id' => null,
    ];

    $this->action->execute($data);
})->throws(\InvalidArgumentException::class, 'Content cannot be empty');
