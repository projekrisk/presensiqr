<?php

namespace App\Models;

use App\Models\Traits\HasSekolah;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsensiHarian extends Model
{
    use HasFactory, HasSekolah;

    protected $table = 'absensi_harian';
    protected $guarded = [];

    protected static function booted()
    {
        static::creating(function ($model) {
            // Logika spesifik: Jika sekolah_id kosong (misal dari API Kiosk),
            // ambil dari sekolah_id milik Siswa yang bersangkutan.
            if (empty($model->sekolah_id) && $model->siswa_id) {
                $siswa = Siswa::find($model->siswa_id);
                if ($siswa) {
                    $model->sekolah_id = $siswa->sekolah_id;
                }
            }
        });
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }
}