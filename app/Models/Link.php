<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
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
}
