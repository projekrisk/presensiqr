<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        // Logika:
        // Cek apakah user sedang login?
        if (Auth::check()) {
            $user = Auth::user();

            // Jika user punya sekolah_id (Artinya dia Admin Sekolah/Guru)
            // Maka filter semua query berdasarkan sekolah_id dia.
            if ($user->sekolah_id) {
                $builder->where('sekolah_id', $user->sekolah_id);
            }
            
            // Jika user->sekolah_id NULL, berarti dia Super Admin.
            // Tidak ada filter (bisa lihat semua).
        }
    }
}
