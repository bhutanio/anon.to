<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LinkAnalytic extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'link_id',
        'visited_at',
        'ip_address',
        'country_code',
        'referrer',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'visited_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the link this analytic belongs to.
     */
    public function link(): BelongsTo
    {
        return $this->belongsTo(Link::class);
    }
}
