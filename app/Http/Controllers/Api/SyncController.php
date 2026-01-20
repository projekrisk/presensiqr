<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\AbsensiHarian;
use App\Models\Perangkat;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    // A. PULL DATA (Download Siswa ke HP)
    public function getSiswa(Request $request)
    {
        // Validasi: Siapa yang minta data?
        // Jika Guru: Ambil dari user()->sekolah_id
        // Jika Kiosk: Ambil dari header 'X-Device-Hash'
        
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

        // Ambil Siswa Aktif Saja
        // PERBAIKAN: Menambahkan 'foto' ke dalam select agar bisa didownload oleh Android
        $siswa = Siswa::where('sekolah_id', $sekolahId)
                      ->where('status_aktif', true)
                      ->select('id', 'nama_lengkap', 'nisn', 'qr_code_data', 'kelas_id', 'foto') 
                      ->with('kelas:id,nama_kelas')
                      ->get();

        return response()->json(['data' => $siswa]);
    }

    // B. PUSH DATA (Upload Absensi dari HP)
    public function uploadAbsensi(Request $request)
    {
        // Menerima Array Data Absensi
        // Format JSON: { "data": [ { "siswa_id": 1, "status": "Hadir", ... }, ... ] }
        
        $data = $request->input('data');
        
        if (!$data || !is_array($data)) {
            return response()->json(['message' => 'Invalid data format'], 400);
        }

        $savedCount = 0;

        foreach ($data as $row) {
            // Simpan ke DB
            // Gunakan updateOrCreate agar tidak double input di hari yang sama
            // Logic Auto-fill sekolah_id ada di Model AbsensiHarian::booted()
            
            AbsensiHarian::updateOrCreate(
                [
                    'siswa_id' => $row['siswa_id'],
                    'tanggal' => $row['tanggal'], // Format YYYY-MM-DD
                ],
                [
                    'jam_masuk' => $row['jam_masuk'],
                    'status' => $row['status'],
                    'sumber' => 'Android',
                    // sekolah_id akan diisi otomatis oleh Model jika null
                ]
            );
            $savedCount++;
        }

        return response()->json(['message' => "Berhasil sinkron $savedCount data"]);
    }
}