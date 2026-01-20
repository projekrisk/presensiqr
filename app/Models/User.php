<?php

namespace App\Models;

// Import Trait Multi-tenant
use App\Models\Traits\HasSekolah;
// Import Sanctum (API)
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    // Pasang Trait HasSekolah & HasApiTokens bersamaan
    use HasApiTokens, HasFactory, Notifiable, HasSekolah;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Method relasi sekolah() TIDAK PERLU ditulis lagi 
    // karena sudah otomatis ada di dalam Trait HasSekolah.
    // Jika ada, hapus agar tidak error "Method sekolah already exists".
}
