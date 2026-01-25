<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\AbsensiHarian;
use App\Models\Perangkat;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Tambahkan Facade DB untuk transaction

class SyncController extends Controller
{
    // A. PULL DATA (Download Siswa & Settings ke HP)
    public function getSiswa(Request $request)
    {
        try {
            $sekolahId = null;

            // 1. Identifikasi Sekolah (Via Guru atau Kiosk)
            if ($request->user()) {
                $sekolahId = $request->user()->sekolah_id;
            } else {
                $deviceHash = $request->header('X-Device-Hash');
                if ($deviceHash) {
                    $perangkat = Perangkat::where('device_id_hash', $deviceHash)->first();
                    if ($perangkat && $perangkat->status_aktif) {
                        $sekolahId = $perangkat->sekolah_id;
                    }
                }
            }

            if (!$sekolahId) {
                return response()->json(['message' => 'Akses Ditolak: Perangkat tidak terdaftar atau sesi berakhir.'], 401);
            }

            // 2. CEK MASA AKTIF SEKOLAH
            $sekolah = Sekolah::find($sekolahId);
            if (!$sekolah || !$sekolah->isSubscriptionActive()) {
                return response()->json([
                    'message' => 'Masa aktif langganan sekolah telah berakhir. Silakan hubungi Admin.'
                ], 403);
            }

            // 3. Ambil Siswa Aktif
            $siswa = Siswa::where('sekolah_id', $sekolahId)
                        ->where('status_aktif', true)
                        ->select('id', 'nama_lengkap', 'nisn', 'qr_code_data', 'kelas_id', 'foto') 
                        ->with('kelas:id,nama_kelas')
                        ->get();

            // 4. Ambil Pengaturan Sekolah
            $settings = [
                'jam_mulai_absen' => $sekolah->jam_mulai_absen,
                'jam_masuk'       => $sekolah->jam_masuk,
                'jam_pulang'      => $sekolah->jam_pulang,
                'hari_kerja'      => $sekolah->hari_kerja,
            ];

            return response()->json([
                'data' => $siswa,
                'settings' => $settings
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan server saat mengambil data: ' . $e->getMessage()], 500);
        }
    }

    // B. PUSH DATA (Upload Absensi dari HP)
    public function uploadAbsensi(Request $request)
    {
        try {
            // 1. Validasi Input
            $data = $request->input('data');
            if (!$data || !is_array($data)) {
                return response()->json(['message' => 'Gagal: Format data yang dikirim tidak valid atau kosong.'], 400);
            }

            // 2. Identifikasi Sekolah & Cek Masa Aktif
            $sekolah = null;
            
            // Cek jika dari Kiosk
            $deviceHash = $request->header('X-Device-Hash');
            if ($deviceHash) {
                $perangkat = Perangkat::where('device_id_hash', $deviceHash)->first();
                if ($perangkat) $sekolah = $perangkat->sekolah;
            }
            // Cek jika dari Guru (Bearer Token)
            elseif ($request->user()) {
                $sekolah = $request->user()->sekolah;
            }

            // Blokir jika sekolah tidak ketemu atau EXPIRED
            if (!$sekolah || !$sekolah->isSubscriptionActive()) {
                return response()->json([
                    'message' => 'Gagal Upload: Masa aktif sekolah berakhir atau perangkat tidak dikenali.'
                ], 403);
            }

            // 3. Proses Simpan dengan Transaction agar aman
            DB::beginTransaction();
            
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
                        'sekolah_id' => $sekolah->id, // Force ID sekolah yang valid
                    ]
                );
                $savedCount++;
            }
            
            DB::commit();

            return response()->json(['message' => "Berhasil menyinkronkan $savedCount data absensi."]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan server saat menyimpan: ' . $e->getMessage()], 500);
        }
    }
}