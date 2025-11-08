<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Note;
use App\Services\CacheKeyService;
use Illuminate\Support\Facades\Cache;

class NoteObserver
{
    /**
     * Handle the Note "created" event.
     */
    public function created(Note $note): void
    {
        // Cache the newly created note
        $this->cacheNote($note);
    }

    /**
     * Handle the Note "updated" event.
     */
    public function updated(Note $note): void
    {
        // Invalidate and refresh cache
        $this->invalidateCache($note);
        $this->cacheNote($note);
    }

    /**
     * Handle the Note "deleted" event.
     */
    public function deleted(Note $note): void
    {
        // Remove from cache
        $this->invalidateCache($note);
    }

    /**
     * Cache a note for fast retrieval.
     */
    protected function cacheNote(Note $note): void
    {
        $ttl = config('anon.default_cache_ttl', 86400);

        Cache::put(CacheKeyService::forNoteModel($note), $note, $ttl);
    }

    /**
     * Invalidate cache for a note.
     */
    protected function invalidateCache(Note $note): void
    {
        Cache::forget(CacheKeyService::forNoteModel($note));
    }
}
