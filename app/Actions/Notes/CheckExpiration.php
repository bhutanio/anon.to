<?php

declare(strict_types=1);

namespace App\Actions\Notes;

use App\Models\Note;

class CheckExpiration
{
    /**
     * Check if a note has expired.
     *
     * @param  Note  $note  The note to check
     * @return bool Returns true if note is expired, false otherwise
     */
    public function execute(Note $note): bool
    {
        // If no expiration date set, note never expires
        if ($note->expires_at === null) {
            return false;
        }

        // Check if expiration date has passed
        return $note->expires_at->isPast();
    }
}
