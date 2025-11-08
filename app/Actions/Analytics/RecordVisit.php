<?php

declare(strict_types=1);

namespace App\Actions\Analytics;

use App\Models\Link;

class RecordVisit
{
    /**
     * Record a visit to a link (increment counter once per session).
     *
     * Only increments the visit counter if this link hasn't been visited
     * in the current session, preventing double-counting on page refreshes.
     */
    public function execute(Link $link): void
    {
        $sessionKey = 'visited_links';
        $visitedLinks = session($sessionKey, []);

        // Check if this link has already been visited in this session
        if (! in_array($link->id, $visitedLinks)) {
            // Atomic increment to prevent race conditions
            $link->increment('visits');

            // Update last visited timestamp
            $link->update(['last_visited_at' => now()]);

            // Mark this link as visited in the current session
            $visitedLinks[] = $link->id;
            session([$sessionKey => $visitedLinks]);
        }

        // Note: Detailed analytics (LinkAnalytic records) will be added in future
        // For now, we just track visit count on the link itself
    }
}
