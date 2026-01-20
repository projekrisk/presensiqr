<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\HasSekolah;

class Perangkat extends Model
{
    use HasFactory;
    use HasSekolah;
    protected $table = 'perangkat';
    protected $guarded = [];

    public function sekolah(): BelongsTo { return $this->belongsTo(Sekolah::class); }
}
