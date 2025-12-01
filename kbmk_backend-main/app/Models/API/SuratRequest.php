<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratRequest extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $fillable = [
        'nomor_surat',
        'nama_pengirim',
        'email_pengirim',
        'perihal',
        'tujuan',
        'asal_instansi',
        'jenis_surat',
        'file_surat',
        'status'
    ];

    public function dispositions()
    {
        return $this->hasMany(SuratDisposition::class);
    }
}