<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Model;

class ProfileDesc extends Model
{
    protected $fillable = [
        'judul',
        'jenis',
        'deskripsi',
    ];
}
