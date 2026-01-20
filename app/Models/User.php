<?php

namespace App\Models;

// --- TAMBAHKAN BARIS INI (1) ---
use Laravel\Sanctum\HasApiTokens; 
// ------------------------------

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\HasSekolah;

class User extends Authenticatable
{
    // --- TAMBAHKAN BARIS INI (2) ---
    use HasApiTokens, HasFactory, Notifiable;
    use HasSekolah;
    // ------------------------------

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}