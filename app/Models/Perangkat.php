<?php

namespace App\Models;

use App\Models\Traits\HasSekolah;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perangkat extends Model
{
    use HasFactory, HasSekolah;

    protected $table = 'perangkat';
    protected $guarded = [];
}