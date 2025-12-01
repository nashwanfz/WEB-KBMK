<?php

namespace App\Models\API;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratOutgoing extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $fillable = [
        'nomor_surat',
        'perihal',
        'tujuan',
        'jenis_surat',
        'file_surat',
        'dibuat_oleh'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }
}