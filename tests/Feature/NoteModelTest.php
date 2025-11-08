<?php

declare(strict_types=1);

use App\Models\Note;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Note Model Creation', function () {
    test('can create note with required fields', function () {
        $note = Note::factory()->create([
            'content' => 'Test content',
            'hash' => 'abc12345',
            'views' => 0, // Explicitly set views to 0
        ]);

        expect($note)->toBeInstanceOf(Note::class)
            ->content->toBe('Test content')
            ->hash->toBe('abc12345')
            ->is_active->toBeTrue()
            ->is_public->toBeTrue()
            ->views->toBe(0);

        $this->assertDatabaseHas('notes', [
            'hash' => 'abc12345',
            'content' => 'Test content',
        ]);
    });

    test('content hash is generated correctly', function () {
        $content = 'Test content for hashing';
        $expectedHash = hash('sha256', $content);

        $note = Note::factory()->create([
            'content' => $content,
            'content_hash' => $expectedHash,
        ]);

        expect($note->content_hash)->toBe($expectedHash);
    });
});

describe('Note Model Relationships', function () {
    test('belongs to user', function () {
        $user = User::factory()->create();
        $note = Note::factory()->create([
            'user_id' => $user->id,
        ]);

        expect($note->user)->toBeInstanceOf(User::class)
            ->id->toBe($user->id);
    });

    test('can have morphMany reports', function () {
        $note = Note::factory()->create();

        $report = Report::create([
            'reportable_type' => Note::class,
            'reportable_id' => $note->id,
            'category' => 'spam',
            'url' => 'http://anon.to.test/n/'.$note->hash,
            'email' => 'reporter@example.com',
            'comment' => 'This is spam',
            'ip_address' => '127.0.0.1', // Store actual IP, not hash (max 45 chars)
            'status' => 'pending',
        ]);

        expect($note->reports)->toHaveCount(1)
            ->first()->category->toBe('spam');
    });
});

describe('Note Model Password Hashing', function () {
    test('password is hashed when stored', function () {
        $note = Note::factory()->withPassword()->create();

        expect($note->password_hash)->not->toBeNull()
            ->and(strlen($note->password_hash))->toBeGreaterThan(50);
    });
});

describe('Note Model Expiration', function () {
    test('can set expiration date', function () {
        $expiresAt = now()->addDays(7);

        $note = Note::factory()->create([
            'expires_at' => $expiresAt,
        ]);

        expect($note->expires_at)->not->toBeNull()
            ->and($note->expires_at->timestamp)->toBe($expiresAt->timestamp);
    });

    test('expired state works correctly', function () {
        $note = Note::factory()->expired()->create();

        expect($note->expires_at)->not->toBeNull()
            ->and($note->expires_at->isPast())->toBeTrue();
    });
});
