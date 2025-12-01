<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\API\Division;
use App\Models\API\SuratDisposition;
use App\Models\API\SuratOutgoing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @OA\Schema(
 *     schema="UserSimple",
 *     description="Schema untuk data user yang disederhanakan, digunakan saat menampilkan user terkait.",
 *     @OA\Property(property="id", type="integer", example=5),
 *     @OA\Property(property="username", type="string", example="koordivhumas"),
 *     @OA\Property(property="email", type="string", format="email", example="koordivhumas@gmail.com"),
 * )
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'roles',
        'username',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function dispositions() 
    {
        return $this->hasMany(SuratDisposition::class, 'assigned_to');
    }

    public function outgoingLetters()
    {
        return $this->hasMany(SuratOutgoing::class, 'dibuat_oleh');
    }
}
