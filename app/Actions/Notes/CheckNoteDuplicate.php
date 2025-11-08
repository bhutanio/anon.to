<?php

declare(strict_types=1);

namespace App\Actions\Notes;

use App\Models\Note;

class CheckNoteDuplicate
{
    /**
     * Check if a note with the same content already exists.
     *
     * Returns the existing note if found, null otherwise.
     *
     * For authenticated users: checks only their own notes.
     * For anonymous users: checks notes created in last 24 hours with same hashed IP.
     *
     * @param  string  $content  The note content to check for duplicates
     * @param  int|null  $userId  The user ID if authenticated
     * @param  string|null  $hashedIp  The hashed IP address for anonymous users
     * @return Note|null The existing note if found, null otherwise
     */
    public function execute(string $content, ?int $userId = null, ?string $hashedIp = null): ?Note
    {
        // Compute SHA256 hash of the content
        $contentHash = hash('sha256', $content);

        // Build query
        $query = Note::where('content_hash', $contentHash)
            ->where('is_active', true);

        // For authenticated users: check only their own notes
        if ($userId !== null) {
            $query->where('user_id', $userId);
        } else {
            // For anonymous users: check notes from last 24 hours with same IP
            $query->where('ip_address', $hashedIp)
                ->where('created_at', '>=', now()->subDay());
        }

        return $query->first();
    }
}
