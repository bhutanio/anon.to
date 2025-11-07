<?php

declare(strict_types=1);

namespace App\Actions\Links;

use App\Models\Link;

class CheckDuplicate
{
    /**
     * Check if a URL already exists in the database.
     *
     * Returns the existing link if found, null otherwise.
     *
     * @param  string  $url  The full URL to check for duplicates
     * @return Link|null The existing link if found, null otherwise
     */
    public function execute(string $url): ?Link
    {
        // Compute SHA256 hash of the full URL
        $urlHash = hash('sha256', $url);

        // Query database for existing link with same URL hash
        return Link::where('full_url_hash', $urlHash)
            ->where('is_active', true)
            ->first();
    }
}
