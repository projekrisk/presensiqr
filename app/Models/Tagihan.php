<?php
namespace App\Models;

use App\Models\Traits\HasSekolah; // PENTING
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tagihan extends Model {
    use HasFactory, HasSekolah;

    protected $table = 'tagihan';
    protected $guarded = [];

    // Auto generate nomor invoice
    protected static function booted() {
        static::creating(function ($model) {
            $model->nomor_invoice = 'INV-' . date('Ymd') . '-' . rand(1000, 9999);
            
            // Auto fill sekolah_id sudah dihandle Trait HasSekolah
        });
    }

    public function sekolah(): BelongsTo { return $this->belongsTo(Sekolah::class); }
    public function paket(): BelongsTo { return $this->belongsTo(Paket::class); }
    public function rekening(): BelongsTo { return $this->belongsTo(Rekening::class); }
}
