<?php

namespace App\Models\API;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratDisposition extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $fillable = [
        'surat_request_id',
        'assigned_to',
        'catatan',
        'status'
    ];

    public function suratRequest()
    {
        return $this->belongsTo(SuratRequest::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}