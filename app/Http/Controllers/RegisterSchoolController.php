<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterSchoolController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'school_name' => 'required|string|max:255',
            'npsn'        => 'required|numeric|unique:sekolah,npsn', // Wajib unik
            'admin_name'  => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',    // Wajib unik
            'phone'       => 'required|string|max:20',
            'password'    => 'required|string|min:8',
        ]);

        try {
            DB::beginTransaction();

            // 2. Buat Data Sekolah Baru
            $sekolah = Sekolah::create([
                'nama_sekolah'    => $request->school_name,
                'npsn'            => $request->npsn,
                'alamat'          => '-', // Bisa diupdate nanti
                'paket_langganan' => 'free', // Default paket gratis
                'status_aktif'    => true,
            ]);

            // 3. Buat User Admin Sekolah
            User::create([
                'name'       => $request->admin_name,
                'email'      => $request->email,
                'password'   => Hash::make($request->password),
                'peran'      => 'admin_sekolah', // Role Admin Sekolah
                'sekolah_id' => $sekolah->id,    // Hubungkan ke sekolah baru
            ]);

            DB::commit();

            // 4. Kembali dengan Pesan Sukses
            return back()->with('success', 'Pendaftaran berhasil! Silakan login menggunakan email dan password Anda.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }
}