<?php

declare(strict_types=1);

namespace App\Actions\Notes;

use App\Models\Note;
use Illuminate\Support\Facades\Cache;

class IncrementViews
{
    /**
     * Increment view count for a note (once per session).
     *
     * Only increments the view counter if this note hasn't been viewed
     * in the current session, preventing double-counting on page refreshes.
     * If view_limit is reached, deletes the note.
     *
     * @param  Note  $note  The note to increment views for
     * @param  string|null  $ipAddress  The IP address of the viewer (unused, kept for backward compatibility)
     * @return bool Returns true if note was deleted due to view limit, false otherwise
     */
    public function execute(Note $note, ?string $ipAddress = null): bool
    {
        $sessionKey = 'viewed_notes';
        $viewedNotes = session($sessionKey, []);

        // Check if this note has already been viewed in this session
        if (! in_array($note->id, $viewedNotes)) {
            // Atomic increment to prevent race conditions
            $note->increment('views');

            // Update last viewed timestamp
            $note->update(['last_viewed_at' => now()]);

            // Mark this note as viewed in the current session
            $viewedNotes[] = $note->id;
            session([$sessionKey => $viewedNotes]);

            // Check view_limit and delete if reached
            if ($note->view_limit !== null && $note->views >= $note->view_limit) {
                // Clear cache
                Cache::forget("note:{$note->hash}");

                // Hard delete note (burn-after-reading)
                $note->delete();

                return true;
            }
        }

        return false;
    }
}
