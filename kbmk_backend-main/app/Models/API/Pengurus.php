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
        'jabatan',
        'division_id',
        'foto',
        'deskripsi'
    ];

    // RELASI KE DIVISION
    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id', 'id');
    }

    // ACCESSOR untuk foto_url
    public function getFotoUrlAttribute()
    {
        if (!$this->foto) {
            return null;
        }

        // Jika foto sudah berupa URL lengkap
        if (filter_var($this->foto, FILTER_VALIDATE_URL)) {
            return $this->foto;
        }

        // Jika foto adalah path relatif
        return asset('storage/' . $this->foto);
    }

    // APPEND foto_url ke array/json output
    protected $appends = ['foto_url'];
}