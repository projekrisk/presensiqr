<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use App\Models\User;
use App\Models\Paket; // Import Model Paket
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon; // Import Carbon untuk tanggal

class RegisterSchoolController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'school_name' => 'required|string|max:255',
            'npsn'        => 'required|numeric|unique:sekolah,npsn',
            'admin_name'  => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'phone'       => 'required|string|max:20',
            'password'    => 'required|string|min:8',
        ]);

        try {
            DB::beginTransaction();

            // --- LOGIKA PAKET FREE OTOMATIS ---
            // Cari paket yang harganya 0 (Free Trial)
            $paketFree = Paket::where('harga', 0)->where('is_active', true)->first();
            
            // Jika paket ketemu, gunakan durasinya. Jika tidak, default 7 hari.
            $durasi = $paketFree ? $paketFree->durasi_hari : 7; 
            $tglBerakhir = Carbon::now()->addDays($durasi);
            // ----------------------------------

            // 2. Buat Data Sekolah Baru
            $sekolah = Sekolah::create([
                'nama_sekolah'           => $request->school_name,
                'npsn'                   => $request->npsn,
                'alamat'                 => '-', 
                'paket_langganan'        => 'free',
                'tgl_berakhir_langganan' => $tglBerakhir, // Set Tanggal Expired
                'status_aktif'           => true,         // Langsung Aktif
            ]);

            // 3. Buat User Admin Sekolah
            User::create([
                'name'       => $request->admin_name,
                'email'      => $request->email,
                'password'   => Hash::make($request->password),
                'peran'      => 'admin_sekolah',
                'sekolah_id' => $sekolah->id,
            ]);

            DB::commit();

            // 4. Kembali dengan Pesan Sukses
            $msg = 'Pendaftaran berhasil! Masa percobaan aktif hingga ' . $tglBerakhir->translatedFormat('d F Y') . '.';
            return back()->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }
}