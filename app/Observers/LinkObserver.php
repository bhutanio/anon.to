<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Link;
use Illuminate\Support\Facades\Cache;

class LinkObserver
{
    /**
     * Handle the Link "creating" event.
     */
    public function creating(Link $link): void
    {
        // Set default values if not already set
        if ($link->visits === null) {
            $link->visits = 0;
        }

        if ($link->unique_visits === null) {
            $link->unique_visits = 0;
        }

        if ($link->is_active === null) {
            $link->is_active = true;
        }

        if ($link->is_reported === null) {
            $link->is_reported = false;
        }
    }

    /**
     * Handle the Link "created" event.
     */
    public function created(Link $link): void
    {
        // Cache the newly created link
        $this->cacheLink($link);
    }

    /**
     * Handle the Link "updated" event.
     */
    public function updated(Link $link): void
    {
        // Invalidate and refresh cache
        $this->invalidateCache($link);
        $this->cacheLink($link);
    }

    /**
     * Handle the Link "deleted" event.
     */
    public function deleted(Link $link): void
    {
        // Remove from cache
        $this->invalidateCache($link);
    }

    /**
     * Cache a link for fast retrieval.
     */
    protected function cacheLink(Link $link): void
    {
        $ttl = config('anon.default_cache_ttl', 86400); // 24 hours

        Cache::put("link:{$link->hash}", $link, $ttl);

        if ($link->slug) {
            Cache::put("link:{$link->slug}", $link, $ttl);
        }
    }

    /**
     * Invalidate cache for a link.
     */
    protected function invalidateCache(Link $link): void
    {
        Cache::forget("link:{$link->hash}");

        if ($link->slug) {
            Cache::forget("link:{$link->slug}");
        }
    }
}
