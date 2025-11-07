<?php

declare(strict_types=1);

namespace App\Actions\Analytics;

use App\Models\Link;

class RecordVisit
{
    /**
     * Record a visit to a link (increment counter).
     *
     * This is a simple implementation for Phase 1-2.
     * Detailed analytics tracking will be added in Phase 8.
     */
    public function execute(Link $link): void
    {
        // Atomic increment to prevent race conditions
        $link->increment('visits');

        // Update last visited timestamp
        $link->update(['last_visited_at' => now()]);

        // Note: Detailed analytics (LinkAnalytic records) will be added in Phase 8
        // For now, we just track visit count on the link itself
    }
}
