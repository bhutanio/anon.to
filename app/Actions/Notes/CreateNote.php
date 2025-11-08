<?php

declare(strict_types=1);

namespace App\Actions\Notes;

use App\Models\Note;
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
     * @param  array{content: string, title: string|null, password: string|null, expires_at: string|null, view_limit: int|null, user_id: int|null}  $data
     *
     * @throws \InvalidArgumentException If validation fails
     * @throws \RuntimeException If hash generation fails
     */
    public function execute(array $data): Note
    {
        $content = $data['content'];
        $title = $data['title'] ?? null;
        $password = $data['password'] ?? null;
        $expiresAt = $data['expires_at'] ?? null;
        $viewLimit = $data['view_limit'] ?? null;
        $userId = $data['user_id'] ?? null;

        // Step 1: Validate input
        $this->validateNote->execute([
            'content' => $content,
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

        // Step 9: Create note record (caching handled by observer)
        return Note::create([
            'hash' => $hash,
            'title' => $title,
            'content' => $content,
            'content_hash' => $contentHash,
            'char_count' => $charCount,
            'line_count' => $lineCount,
            'password_hash' => $passwordHash,
            'expires_at' => $expiresAt,
            'view_limit' => $viewLimit,
            'views' => 0,
            'last_viewed_at' => null,
            'is_active' => true,
            'is_reported' => false,
            'is_public' => true,
            'user_id' => $userId,
            'ip_address' => $hashedIp,
        ]);
    }
}
