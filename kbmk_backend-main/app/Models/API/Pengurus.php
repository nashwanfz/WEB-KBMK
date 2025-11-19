<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Pengurus extends Model
{
    use HasFactory;

    protected $table = 'pengurus';

    protected $fillable = [
        'nama',
        'divisi',
        'foto',
        'deskripsi',
    ];

    public function getFotoUrlAttribute()
    {
        return $this->foto ? Storage::url($this->foto) : null;
    }
}