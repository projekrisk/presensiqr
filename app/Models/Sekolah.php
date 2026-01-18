<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sekolah extends Model
{
    use HasFactory;

    // Paksa nama tabel jadi singular
    protected $table = 'sekolah';

    // Izinkan semua kolom diisi
    protected $guarded = [];

    // Relasi: Satu Sekolah punya banyak User (Guru)
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'sekolah_id');
    }
}
