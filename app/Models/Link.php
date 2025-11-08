<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Link extends Model
{
    use HasFactory;

    protected $fillable = [
        'hash',
        'url_scheme',
        'url_host',
        'url_port',
        'url_path',
        'url_query',
        'url_fragment',
        'full_url',
        'full_url_hash',
        'title',
        'description',
        'visits',
        'last_visited_at',
        'is_active',
        'is_reported',
        'user_id',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'last_visited_at' => 'datetime',
            'is_active' => 'boolean',
            'is_reported' => 'boolean',
            'visits' => 'integer',
            'url_port' => 'integer',
        ];
    }

    /**
     * Get the user that created the link.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the analytics for the link.
     */
    public function analytics(): HasMany
    {
        return $this->hasMany(LinkAnalytic::class);
    }

    /**
     * Get all of the link's reports.
     */
    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }
}
