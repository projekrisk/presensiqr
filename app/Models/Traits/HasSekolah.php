<?php

namespace App\Models\Traits;

use App\Models\Scopes\TenantScope;
use App\Models\Sekolah;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait HasSekolah
{
    // Saat Model "Booting", pasang Scope otomatis
    protected static function bootHasSekolah()
    {
        static::addGlobalScope(new TenantScope);

        // Otomatis isi sekolah_id saat Create Data Baru
        static::creating(function ($model) {
            if (Auth::hasUser() && Auth::user()->sekolah_id) {
                $model->sekolah_id = Auth::user()->sekolah_id;
            }
        });
    }

    // Definisi Relasi standar ke Sekolah
    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }
}