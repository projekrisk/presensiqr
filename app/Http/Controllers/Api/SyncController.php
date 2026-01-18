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
            // Request dari Guru (ada Token)
            $sekolahId = $request->user()->sekolah_id;
        } else {
            // Request dari Kiosk (Cek Hash)
            $deviceHash = $request->header('X-Device-Hash');
            $perangkat = Perangkat::where('device_id_hash', $deviceHash)->first();
            if ($perangkat) $sekolahId = $perangkat->sekolah_id;
        }

        if (!$sekolahId) return response()->json(['message' => 'Unauthorized'], 401);

        // Ambil Siswa Aktif Saja
        $siswa = Siswa::where('sekolah_id', $sekolahId)
                      ->where('status_aktif', true)
                      ->select('id', 'nama_lengkap', 'nisn', 'qr_code_data', 'kelas_id') // Hemat bandwidth
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
        $savedCount = 0;

        foreach ($data as $row) {
            // Cari ID Siswa berdasarkan QR Hash (jika yang dikirim hash)
            // Atau pakai ID jika Android sudah punya ID lokal

            // Simpan ke DB
            // Gunakan updateOrCreate agar tidak double input di hari yang sama
            AbsensiHarian::updateOrCreate(
                [
                    'siswa_id' => $row['siswa_id'],
                    'tanggal' => $row['tanggal'], // YYYY-MM-DD
                ],
                [
                    'jam_masuk' => $row['jam_masuk'],
                    'status' => $row['status'],
                    'sumber' => 'Android',
                    'sekolah_id' => $row['sekolah_id'] // Atau ambil otomatis dari relasi siswa
                ]
            );
            $savedCount++;
        }

        return response()->json(['message' => "Berhasil sinkron $savedCount data"]);
    }
}
