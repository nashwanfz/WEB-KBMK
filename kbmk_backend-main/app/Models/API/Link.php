<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    protected $table = 'links';

    protected $fillable = [
        'nama',
        'link'
    ];
}