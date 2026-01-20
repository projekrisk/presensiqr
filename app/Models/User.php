<?php

namespace App\Models;

// PENTING: JANGAN IMPORT HasSekolah DI SINI!
// Menggunakan Global Scope pada User model memicu Infinite Loop saat Auth login.

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Logika Auto-Fill Sekolah ID (Manual tanpa Trait)
    // Ini aman karena hanya jalan saat "creating", bukan saat "retrieving" (login)
    protected static function booted()
    {
        static::creating(function ($model) {
            if (Auth::hasUser() && Auth::user()->sekolah_id) {
                $model->sekolah_id = Auth::user()->sekolah_id;
            }
        });
    }

    // Relasi Manual (Karena tidak pakai Trait)
    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class, 'sekolah_id');
    }
}