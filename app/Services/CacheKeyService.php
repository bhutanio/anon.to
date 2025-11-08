<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Link;
use App\Models\Note;

class CacheKeyService
{
    /**
     * Get cache key for a link by hash.
     */
    public static function forLink(string $hash): string
    {
        return "link:{$hash}";
    }

    /**
     * Get cache key for a link model.
     */
    public static function forLinkModel(Link $link): string
    {
        return self::forLink($link->hash);
    }

    /**
     * Get cache key for a note by hash.
     */
    public static function forNote(string $hash): string
    {
        return "note:{$hash}";
    }

    /**
     * Get cache key for a note model.
     */
    public static function forNoteModel(Note $note): string
    {
        return self::forNote($note->hash);
    }
}
