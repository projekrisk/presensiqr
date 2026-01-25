<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sekolah extends Model
{
    use HasFactory;

    protected $table = 'sekolah';
    
    protected $guarded = [];

    // PERBAIKAN: Tambahkan casting untuk tanggal
    protected $casts = [
        'hari_kerja' => 'array',
        'status_aktif' => 'boolean',
        'tgl_berakhir_langganan' => 'date', // <--- PENTING: Agar dibaca sebagai tanggal
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'sekolah_id');
    }
    
    // Fungsi cek aktif
    public function isSubscriptionActive(): bool
    {
        if (!$this->status_aktif) return false;
        if ($this->tgl_berakhir_langganan === null) return true;
        
        // Menggunakan now()->startOfDay() agar jam tidak mempengaruhi (expired di akhir hari)
        return \Illuminate\Support\Carbon::now()->startOfDay()->lte($this->tgl_berakhir_langganan);
    }
}