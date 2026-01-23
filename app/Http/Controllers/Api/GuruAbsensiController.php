<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbsensiHarian;
use App\Models\Kelas;
use Illuminate\Http\Request;

class GuruAbsensiController extends Controller
{
    /**
     * Mengambil data absensi berdasarkan Tanggal dan Nama Kelas
     * Endpoint: GET /api/guru/absensi-check
     */
    public function check(Request $request)
    {
        $user = $request->user(); // Guru yang login
        
        // Validasi Input
        $request->validate([
            'tanggal' => 'required|date',
            'kelas' => 'required|string',
        ]);

        $tanggal = $request->query('tanggal');
        $namaKelas = $request->query('kelas');

        // 1. Cari ID Kelas berdasarkan Nama Kelas (dan Sekolah Guru tersebut)
        $kelas = Kelas::where('nama_kelas', $namaKelas)
                      ->where('sekolah_id', $user->sekolah_id)
                      ->first();

        if (!$kelas) {
            // Jika kelas tidak ditemukan, kembalikan list kosong
            return response()->json([]);
        }

        // 2. Ambil Data Absensi
        // Kita cari data di tabel absensi_harian yang:
        // - Tanggalnya sesuai
        // - Siswanya berada di kelas yang dicari
        $dataAbsensi = AbsensiHarian::where('tanggal', $tanggal)
            ->whereHas('siswa', function ($query) use ($kelas) {
                $query->where('kelas_id', $kelas->id);
            })
            ->get()
            ->map(function ($item) {
                // Format response sesuai model AbsensiItem di Android
                return [
                    'siswa_id' => $item->siswa_id,
                    'tanggal' => $item->tanggal,
                    'jam_masuk' => $item->jam_masuk,
                    'status' => $item->status,
                    'sekolah_id' => $item->sekolah_id,
                ];
            });

        return response()->json($dataAbsensi);
    }
}
