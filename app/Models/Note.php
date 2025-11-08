<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'hash',
        'title',
        'content',
        'content_hash',
        'char_count',
        'line_count',
        'expires_at',
        'password_hash',
        'view_limit',
        'views',
        'unique_views',
        'last_viewed_at',
        'is_active',
        'is_reported',
        'is_public',
        'user_id',
        'forked_from_id',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'last_viewed_at' => 'datetime',
            'is_active' => 'boolean',
            'is_reported' => 'boolean',
            'is_public' => 'boolean',
            'char_count' => 'integer',
            'line_count' => 'integer',
            'view_limit' => 'integer',
            'views' => 'integer',
            'unique_views' => 'integer',
        ];
    }

    /**
     * Get the user that created the note.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent note if this note was forked.
     */
    public function parentNote(): BelongsTo
    {
        return $this->belongsTo(Note::class, 'forked_from_id');
    }

    /**
     * Get the child notes that were forked from this note.
     */
    public function forks(): HasMany
    {
        return $this->hasMany(Note::class, 'forked_from_id');
    }

    /**
     * Get all of the note's reports.
     */
    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }
}
