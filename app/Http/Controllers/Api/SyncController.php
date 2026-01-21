<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\AbsensiHarian;
use App\Models\Perangkat;
use App\Models\Sekolah; // Import Model Sekolah
use Illuminate\Http\Request;

class SyncController extends Controller
{
    // A. PULL DATA (Download Siswa & Settings ke HP)
    public function getSiswa(Request $request)
    {
        // Validasi: Siapa yang minta data?
        $sekolahId = null;

        if ($request->user()) {
            // Request dari Guru (ada Token Sanctum)
            $sekolahId = $request->user()->sekolah_id;
        } else {
            // Request dari Kiosk (Cek Hash Device)
            $deviceHash = $request->header('X-Device-Hash');
            
            if ($deviceHash) {
                $perangkat = Perangkat::where('device_id_hash', $deviceHash)->first();
                if ($perangkat && $perangkat->status_aktif) {
                    $sekolahId = $perangkat->sekolah_id;
                }
            }
        }

        if (!$sekolahId) {
            return response()->json(['message' => 'Unauthorized / Device Not Registered'], 401);
        }

        // 1. Ambil Siswa Aktif
        $siswa = Siswa::where('sekolah_id', $sekolahId)
                      ->where('status_aktif', true)
                      ->select('id', 'nama_lengkap', 'nisn', 'qr_code_data', 'kelas_id', 'foto') 
                      ->with('kelas:id,nama_kelas')
                      ->get();

        // 2. Ambil Pengaturan Sekolah (Jam & Hari)
        $sekolah = Sekolah::find($sekolahId);
        $settings = [
            'jam_mulai_absen' => $sekolah->jam_mulai_absen,
            'jam_masuk'       => $sekolah->jam_masuk,
            'jam_pulang'      => $sekolah->jam_pulang,
            'hari_kerja'      => $sekolah->hari_kerja, // Array
        ];

        return response()->json([
            'data' => $siswa,
            'settings' => $settings // Kirim settings ke Android
        ]);
    }

    // B. PUSH DATA (Upload Absensi dari HP)
    public function uploadAbsensi(Request $request)
    {
        $data = $request->input('data');
        
        if (!$data || !is_array($data)) {
            return response()->json(['message' => 'Invalid data format'], 400);
        }

        $savedCount = 0;

        foreach ($data as $row) {
            AbsensiHarian::updateOrCreate(
                [
                    'siswa_id' => $row['siswa_id'],
                    'tanggal' => $row['tanggal'], 
                ],
                [
                    'jam_masuk' => $row['jam_masuk'],
                    'status' => $row['status'],
                    'sumber' => 'Android',
                ]
            );
            $savedCount++;
        }

        return response()->json(['message' => "Berhasil sinkron $savedCount data"]);
    }
}
