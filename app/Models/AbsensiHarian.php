<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\HasSekolah;

class AbsensiHarian extends Model
{
    use HasFactory;
    use HasSekolah;

    protected $table = 'absensi_harian';
    protected $guarded = [];

    // --- LOGIKA OTOMATIS (AUTO FILL) ---
    // Kode ini mencegah error "Field sekolah_id doesn't have a default value"
    protected static function booted()
    {
        static::creating(function ($model) {
            // Jika sekolah_id kosong, ambil otomatis dari data siswa yang dipilih
            if (empty($model->sekolah_id) && $model->siswa_id) {
                $siswa = Siswa::find($model->siswa_id);
                if ($siswa) {
                    $model->sekolah_id = $siswa->sekolah_id;
                }
            }
        });
    }

    // Relasi ke Siswa
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    // Relasi ke Sekolah
    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }
}
