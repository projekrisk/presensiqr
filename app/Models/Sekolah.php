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

    // Konversi otomatis JSON ke Array PHP dan Boolean
    protected $casts = [
        'hari_kerja' => 'array',
        'status_aktif' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'sekolah_id');
    }
}
