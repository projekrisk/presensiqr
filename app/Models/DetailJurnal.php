<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailJurnal extends Model
{
    use HasFactory;

    protected $table = 'detail_jurnal';
    protected $guarded = [];

    /**
     * Relasi ke Tabel Induk (JurnalGuru).
     * Penting untuk mengambil data Tanggal dan Mata Pelajaran di Laporan.
     */
    public function jurnal(): BelongsTo
    {
        return $this->belongsTo(JurnalGuru::class, 'jurnal_guru_id');
    }

    /**
     * Relasi ke Siswa.
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }
}