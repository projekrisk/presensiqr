<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon; // Import Carbon

class Sekolah extends Model
{
    use HasFactory;
    protected $table = 'sekolah';
    protected $guarded = [];

    protected $casts = [
        'hari_kerja' => 'array',
        'status_aktif' => 'boolean',
    ];

    public function users(): HasMany { return $this->hasMany(User::class, 'sekolah_id'); }

    // --- LOGIKA CEK LANGGANAN ---
    public function isSubscriptionActive(): bool
    {
        // Jika status manual non-aktif (dibanned admin), tolak langsung
        if (!$this->status_aktif) return false;

        // Jika tanggal null (Trial/Unlimited), dianggap aktif
        if ($this->tgl_berakhir_langganan === null) return true;

        // Cek apakah tanggal sekarang belum melewati tanggal berakhir
        // startOfDay() agar expired tepat jam 00:00 besoknya
        return Carbon::now()->startOfDay()->lte(Carbon::parse($this->tgl_berakhir_langganan));
    }
}