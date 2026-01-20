<?php

namespace App\Models;

use App\Models\Traits\HasSekolah; // <--- WAJIB IMPORT INI
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Perangkat extends Model
{
    use HasFactory, HasSekolah; // <--- WAJIB PASANG TRAIT INI

    protected $table = 'perangkat';
    protected $guarded = [];
}
