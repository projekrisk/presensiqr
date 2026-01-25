<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use App\Models\User;
use App\Models\Paket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class RegisterSchoolController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi Input (Tambahan: Logo Wajib)
        $request->validate([
            'school_name' => 'required|string|max:255',
            'npsn'        => 'required|numeric|unique:sekolah,npsn',
            'logo'        => 'required|image|mimes:jpeg,png,jpg|max:2048', // Wajib, Max 2MB
            
            'admin_name'  => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'phone'       => 'required|string|max:20',
            'password'    => 'required|string|min:8',
        ]);

        try {
            DB::beginTransaction();

            // 2. Upload Logo
            $logoPath = null;
            if ($request->hasFile('logo')) {
                // Simpan di disk 'uploads' folder 'sekolah-logo'
                $logoPath = $request->file('logo')->store('sekolah-logo', 'uploads');
            }

            // 3. Logika Paket Free
            $paketFree = Paket::where('harga', 0)->where('is_active', true)->first();
            $durasi = $paketFree ? $paketFree->durasi_hari : 7; 
            $tglBerakhir = Carbon::now()->addDays($durasi);

            // 4. Buat Data Sekolah
            $sekolah = Sekolah::create([
                'nama_sekolah'           => $request->school_name,
                'npsn'                   => $request->npsn,
                'alamat'                 => '-', 
                'logo'                   => $logoPath, // Simpan Path Logo
                'paket_langganan'        => 'free',
                'tgl_berakhir_langganan' => $tglBerakhir,
                'status_aktif'           => true,
            ]);

            // 5. Buat User Admin
            User::create([
                'name'       => $request->admin_name,
                'email'      => $request->email,
                'password'   => Hash::make($request->password),
                'peran'      => 'admin_sekolah',
                'sekolah_id' => $sekolah->id,
            ]);

            DB::commit();

            return back()->with('success', 'Pendaftaran berhasil! Silakan login.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mendaftar: ' . $e->getMessage())->withInput();
        }
    }
}