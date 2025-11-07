<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllowList extends Model
{
    use HasFactory;

    protected $fillable = [
        'domain',
        'type',
        'pattern_type',
        'reason',
        'is_active',
        'hit_count',
        'added_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'hit_count' => 'integer',
        ];
    }

    /**
     * Get the admin who added this rule.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
