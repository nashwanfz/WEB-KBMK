<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'nama',
        'tanggal',
        'deskripsi',
    ];
}
