<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas'; // Paksa nama singular
    protected $guarded = [];

    // Relasi: Kelas milik Sekolah
    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    // Relasi: Kelas punya banyak Siswa
    public function siswa(): HasMany
    {
        return $this->hasMany(Siswa::class, 'kelas_id');
    }
}
