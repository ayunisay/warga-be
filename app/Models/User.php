<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\EncryptsSensitiveAttributes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, EncryptsSensitiveAttributes;

    protected $fillable = [
        'name',
        'email',
        'nik',
        'nik_bindex',
        'password',
        'role',
        'jabatan',
        'telepon',
        'avatar_initial',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'nik_bindex',
    ];

    /**
     * Enkripsi AES-256 At-Rest (UU PDP)
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'nik' => 'encrypted',
        'telepon' => 'encrypted',
    ];

    protected $appends = [
        'nikMasked',
        'teleponMasked',
    ];

    public function citizen()
    {
        return $this->hasOne(Citizen::class, 'nik_bindex', 'nik_bindex');
    }
}