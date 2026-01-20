<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\HasSekolah;

class JurnalGuru extends Model
{
    use HasFactory;
    use HasSekolah;

    protected $table = 'jurnal_guru';
    protected $guarded = [];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function kelas(): BelongsTo { return $this->belongsTo(Kelas::class); }
    public function detail(): HasMany { return $this->hasMany(DetailJurnal::class, 'jurnal_guru_id'); }
}
