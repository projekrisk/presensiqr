<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa';
    protected $guarded = [];

    // --- LOGIKA OTOMATIS GENERATE QR ---
    protected static function booted()
    {
        static::saving(function ($siswa) {
            // Jika NISN atau Nama berubah, generate ulang QR
            // Format QR: SHA256(NISN + Nama + SecretKeyAplikasi)
            // Ini aman karena tidak bisa ditebak siswa.

            $dataMentah = $siswa->nisn . '_' . $siswa->nama_lengkap . '_' . env('APP_KEY');
            $siswa->qr_code_data = hash('sha256', $dataMentah);
        });
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
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
