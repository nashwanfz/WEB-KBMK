<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    use HasFactory;

    protected $table = 'divisions';

    protected $fillable = [
        'nama',
        'deskripsi'
    ];

    // RELASI KE PENGURUS
    public function pengurus()
    {
        return $this->hasMany(Pengurus::class, 'division_id', 'id');
    }
}