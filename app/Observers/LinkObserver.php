<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Link;
use App\Services\CacheKeyService;
use Illuminate\Support\Facades\Cache;

class LinkObserver
{
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
        $ttl = config('anon.default_cache_ttl', 86400);

        Cache::put(CacheKeyService::forLinkModel($link), $link, $ttl);
    }

    /**
     * Invalidate cache for a link.
     */
    protected function invalidateCache(Link $link): void
    {
        Cache::forget(CacheKeyService::forLinkModel($link));
    }
}
