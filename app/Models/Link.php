<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Links extends Model
{
    protected $table = 'links';

    protected $fillable = [
        'hash',
        'url_scheme',
        'url_host',
        'url_port',
        'url_path',
        'url_query',
        'url_fragment',
        'created_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
