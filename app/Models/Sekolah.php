<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Sekolah extends Model
{
    use HasFactory;
    protected $table = 'sekolah';
    protected $guarded = [];

    protected $casts = [
        'hari_kerja' => 'array',
        'status_aktif' => 'boolean',
        'tgl_berakhir_langganan' => 'date',
    ];

    public function users(): HasMany { return $this->hasMany(User::class, 'sekolah_id'); }

    // --- LOGIKA CEK LANGGANAN (DIPERBARUI) ---
    public function isSubscriptionActive(): bool
    {
        // 1. Cek Status Switch Manual
        if (!$this->status_aktif) return false;

        // 2. Cek Tanggal
        // PERBAIKAN: Jika tanggal NULL, anggap EXPIRED (Wajib ada tanggal)
        if ($this->tgl_berakhir_langganan === null) return false;

        // 3. Cek Tanggal Expired
        // Menggunakan endOfDay() agar paket berlaku sampai detik terakhir hari tersebut
        return Carbon::now()->lte($this->tgl_berakhir_langganan->endOfDay());
    }
}