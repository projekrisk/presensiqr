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

    public function siswa(): BelongsTo 
    { 
        return $this->belongsTo(Siswa::class); 
    }
}
