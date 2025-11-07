<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'reportable_type',
        'reportable_id',
        'category',
        'url',
        'email',
        'comment',
        'ip_address',
        'user_id',
        'status',
        'admin_notes',
        'dealt_by',
        'dealt_at',
    ];

    protected function casts(): array
    {
        return [
            'dealt_at' => 'datetime',
        ];
    }

    /**
     * Get the reportable model (Link or Note).
     */
    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who submitted the report.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who dealt with the report.
     */
    public function dealtBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dealt_by');
    }
}
