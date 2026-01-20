<?php

namespace App\Models;

use App\Models\Traits\HasSekolah; // Import Trait
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Siswa extends Model
{
    use HasFactory, HasSekolah; // Pasang Trait

    protected $table = 'siswa';
    protected $guarded = [];

    // Logika QR Code
    protected static function booted()
    {
        static::saving(function ($siswa) {
            if ($siswa->isDirty('nisn') || $siswa->isDirty('nama_lengkap')) {
                 $dataMentah = $siswa->nisn . '_' . $siswa->nama_lengkap . '_' . env('APP_KEY');
                 $siswa->qr_code_data = hash('sha256', $dataMentah);
            }
        });
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function absensi(): HasMany
    {
        return $this->hasMany(AbsensiHarian::class, 'siswa_id');
    }
}