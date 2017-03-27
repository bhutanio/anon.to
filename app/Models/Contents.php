<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contents extends Model
{
    protected $table = 'contents';

    protected $fillable = [
        'title',
        'title_slug',
        'content',
    ];
}
