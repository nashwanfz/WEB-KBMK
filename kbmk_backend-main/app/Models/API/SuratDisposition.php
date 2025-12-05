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

    /**
     * Get the surat request that owns the disposition.
     */
    public function suratRequest()
    {
        return $this->belongsTo(SuratRequest::class);
    }

    /**
     * Get the user that is assigned the disposition.
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}