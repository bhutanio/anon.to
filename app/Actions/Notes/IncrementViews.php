<?php

declare(strict_types=1);

namespace App\Actions\Notes;

use App\Models\Note;
use Illuminate\Support\Facades\Cache;

class IncrementViews
{
    /**
     * Increment view count for a note.
     *
     * Tracks total views and unique views. If view_limit is reached, deletes the note.
     *
     * @param  Note  $note  The note to increment views for
     * @param  string|null  $ipAddress  The IP address of the viewer (optional)
     * @return bool Returns true if note was deleted due to view limit, false otherwise
     */
    public function execute(Note $note, ?string $ipAddress = null): bool
    {
        // Increment views counter
        $note->views++;

        // Track unique views if IP address provided
        if ($ipAddress !== null) {
            $cacheKey = "note-viewed:{$note->hash}:".hash('sha256', $ipAddress);

            // Check if this IP has viewed before
            if (! Cache::has($cacheKey)) {
                $note->unique_views++;

                // Store flag for 24 hours
                Cache::put($cacheKey, true, 86400);
            }
        }

        // Update last_viewed_at timestamp
        $note->last_viewed_at = now();

        // Save changes
        $note->save();

        // Check view_limit and delete if reached
        if ($note->view_limit !== null && $note->views >= $note->view_limit) {
            // Clear cache
            Cache::forget("note:{$note->hash}");

            // Hard delete note (burn-after-reading)
            $note->delete();

            return true;
        }

        return false;
    }
}
