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
        // PENTING: Gunakan hasUser() bukan check() untuk mencegah Infinite Loop
        // pada saat Auth mencoba mengambil user dari session.
        if (!Auth::hasUser()) {
            return;
        }

        $user = Auth::user();

        // Jika user punya sekolah_id (Artinya dia Admin Sekolah/Guru)
        if ($user->sekolah_id) {
            // Tambahkan nama tabel untuk mencegah error "Ambiguous column" saat Join
            $table = $model->getTable();
            $builder->where("$table.sekolah_id", $user->sekolah_id);
        }
        
        // Jika user->sekolah_id NULL (Super Admin), tidak ada filter (bisa lihat semua).
    }
}