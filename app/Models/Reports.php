<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reports extends Model
{
    protected $table = 'link_reports';

    protected $fillable = [
        'link_id',
        'url',
        'email',
        'comment',
        'ip_address',
        'created_by',
    ];

    public function link()
    {
        return $this->belongsTo(Links::class, 'link_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
