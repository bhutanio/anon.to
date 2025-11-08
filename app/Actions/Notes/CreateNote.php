<?php

declare(strict_types=1);

namespace App\Actions\Notes;

use App\Models\Note;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class CreateNote
{
    public function __construct(
        protected ValidateNote $validateNote,
        protected GenerateNoteHash $generateHash,
        protected CheckNoteDuplicate $checkDuplicate,
    ) {}

    /**
     * Create a new note.
     *
     * @param  array{content: string, syntax: string|null, title: string|null, password: string|null, expires_at: string|null, view_limit: int|null, user_id: int|null}  $data
     *
     * @throws \InvalidArgumentException If validation fails
     * @throws \RuntimeException If hash generation fails
     */
    public function execute(array $data): Note
    {
        $content = $data['content'];
        $syntax = $data['syntax'] ?? null;
        $title = $data['title'] ?? null;
        $password = $data['password'] ?? null;
        $expiresAt = $data['expires_at'] ?? null;
        $viewLimit = $data['view_limit'] ?? null;
        $userId = $data['user_id'] ?? null;

        // Step 1: Validate input
        $this->validateNote->execute([
            'content' => $content,
            'syntax' => $syntax,
            'title' => $title,
        ]);

        // Step 2: Hash IP address for privacy
        $ipAddress = request()->ip();
        $hashedIp = $ipAddress ? hash('sha256', $ipAddress) : null;

        // Step 3: Check for duplicate
        $existingNote = $this->checkDuplicate->execute($content, $userId, $hashedIp);
        if ($existingNote) {
            // Return existing note instead of creating duplicate
            return $existingNote;
        }

        // Step 4: Generate unique hash
        $hash = $this->generateHash->execute();

        // Step 5: Calculate char_count
        $charCount = mb_strlen($content);

        // Step 6: Calculate line_count
        $lineCount = substr_count($content, "\n") + 1;

        // Step 7: Generate content_hash
        $contentHash = hash('sha256', $content);

        // Step 8: Hash password if provided
        $passwordHash = $password ? Hash::make($password) : null;

        // Step 9: Set is_code based on syntax
        $isCode = $syntax !== null && $syntax !== 'plaintext';

        // Step 10: Create note record
        $note = Note::create([
            'hash' => $hash,
            'title' => $title,
            'content' => $content,
            'content_hash' => $contentHash,
            'syntax' => $syntax,
            'char_count' => $charCount,
            'line_count' => $lineCount,
            'password_hash' => $passwordHash,
            'expires_at' => $expiresAt,
            'view_limit' => $viewLimit,
            'views' => 0,
            'unique_views' => 0,
            'last_viewed_at' => null,
            'is_active' => true,
            'is_reported' => false,
            'is_public' => true,
            'is_code' => $isCode,
            'user_id' => $userId,
            'ip_address' => $hashedIp,
            'user_agent' => request()->userAgent(),
        ]);

        // Step 11: Cache note for fast retrieval
        $this->cacheNote($note);

        return $note;
    }

    /**
     * Cache a note for fast retrieval.
     */
    protected function cacheNote(Note $note): void
    {
        $ttl = config('anon.default_cache_ttl', 86400); // 24 hours

        Cache::put("note:{$note->hash}", $note, $ttl);
    }
}
