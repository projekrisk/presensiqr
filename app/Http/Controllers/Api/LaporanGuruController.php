<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetailJurnal; // Gunakan ini, bukan AbsensiHarian
use App\Models\JurnalGuru;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanGuruController extends Controller
{
    // Endpoint: GET /api/guru/laporan/summary
    public function summary(Request $request)
    {
        $request->validate([
            'bulan' => 'required|numeric',
            'tahun' => 'required|numeric',
            'kelas' => 'required|string',
        ]);

        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $namaKelas = $request->kelas;
        $user = $request->user();

        // 1. Cari ID Kelas
        $kelas = Kelas::where('nama_kelas', $namaKelas)
                      ->where('sekolah_id', $user->sekolah_id)
                      ->first();

        if (!$kelas) {
            return response()->json([], 200);
        }

        // 2. Ambil Siswa di kelas tersebut
        $siswaIds = Siswa::where('kelas_id', $kelas->id)->pluck('id');

        // 3. PERBAIKAN: Hitung Statistik dari DetailJurnal (Bukan AbsensiHarian)
        // Join dengan tabel induk (jurnal_guru) untuk filter tanggal
        $laporan = DetailJurnal::whereIn('siswa_id', $siswaIds)
            ->whereHas('jurnal', function ($query) use ($bulan, $tahun) {
                $query->whereMonth('tanggal', $bulan)
                      ->whereYear('tanggal', $tahun);
            })
            ->whereIn('status', ['Alpha', 'Sakit', 'Izin'])
            ->select('siswa_id', DB::raw('count(*) as total'))
            ->groupBy('siswa_id')
            ->get();

        // 4. Format Data
        $hasil = [];
        
        foreach ($laporan as $rekap) {
            $siswa = Siswa::find($rekap->siswa_id);
            
            // Helper query untuk hitung per status
            // Kita bungkus dalam fungsi anonymous biar rapi
            $countStatus = function($status) use ($rekap, $bulan, $tahun) {
                return DetailJurnal::where('siswa_id', $rekap->siswa_id)
                    ->whereHas('jurnal', function ($q) use ($bulan, $tahun) {
                        $q->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
                    })
                    ->where('status', $status)->count();
            };

            $hasil[] = [
                'siswa_id' => $siswa->id,
                'nama_siswa' => $siswa->nama_lengkap,
                'nisn' => $siswa->nisn,
                'total_tidak_hadir' => $rekap->total,
                'total_alpha' => $countStatus('Alpha'),
                'total_sakit' => $countStatus('Sakit'),
                'total_izin' => $countStatus('Izin'),
            ];
        }

        return response()->json($hasil);
    }

    // Endpoint: GET /api/guru/laporan/detail
    public function detail(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|numeric',
            'bulan' => 'required|numeric',
            'tahun' => 'required|numeric',
        ]);

        // PERBAIKAN: Ambil dari DetailJurnal
        $detail = DetailJurnal::where('siswa_id', $request->siswa_id)
            ->whereHas('jurnal', function ($q) use ($request) {
                $q->whereMonth('tanggal', $request->bulan)
                  ->whereYear('tanggal', $request->tahun);
            })
            ->with('jurnal') // Load parent untuk ambil tanggal
            ->whereIn('status', ['Alpha', 'Sakit', 'Izin'])
            ->get()
            ->map(function ($item) {
                return [
                    // Ambil tanggal dari tabel parent (JurnalGuru)
                    'tanggal' => date('d M Y', strtotime($item->jurnal->tanggal)),
                    'status' => $item->status,
                    'keterangan' => $item->jurnal->mata_pelajaran ?? '-', // Tampilkan Mapel sebagai info
                ];
            })
            // Sort manual karena tanggal ada di relation
            ->sortBy('tanggal')
            ->values(); // Reset keys

        return response()->json($detail);
    }
}