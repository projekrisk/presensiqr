<?php

namespace App\Models;

use App\Models\Traits\HasSekolah; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class JurnalGuru extends Model
{
    use HasFactory, HasSekolah; 

    protected $table = 'jurnal_guru';
    protected $guarded = [];

    // PERBAIKAN: Casting tanggal agar dibaca sebagai Carbon Object
    protected $casts = [
        'tanggal' => 'date',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->user_id = Auth::id();
            }
        });
    }

    public function user(): BelongsTo 
    { 
        return $this->belongsTo(User::class); 
    }
    
    public function kelas(): BelongsTo 
    { 
        return $this->belongsTo(Kelas::class); 
    }
    
    public function detail(): HasMany 
    { 
        return $this->hasMany(DetailJurnal::class, 'jurnal_guru_id'); 
    }
}