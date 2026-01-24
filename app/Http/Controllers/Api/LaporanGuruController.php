<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbsensiHarian;
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
            return response()->json([], 200); // Kelas tidak ketemu
        }

        // 2. Ambil Siswa di kelas tersebut
        $siswaIds = Siswa::where('kelas_id', $kelas->id)->pluck('id');

        // 3. Hitung Statistik Absensi dari tabel AbsensiHarian
        // Kita gunakan GROUP BY siswa_id untuk menghitung total per siswa
        $laporan = AbsensiHarian::whereIn('siswa_id', $siswaIds)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->whereIn('status', ['Alpha', 'Sakit', 'Izin']) // Hanya ambil yang tidak hadir
            ->select('siswa_id', DB::raw('count(*) as total'))
            ->groupBy('siswa_id')
            ->get();

        // 4. Format Data untuk Android
        $hasil = [];
        
        // Loop semua siswa yang punya record tidak hadir
        // (Siswa yang hadir terus tidak perlu ditampilkan di list "Ketidakhadiran")
        foreach ($laporan as $rekap) {
            $siswa = Siswa::find($rekap->siswa_id);
            
            // Hitung detail per status
            $alpha = AbsensiHarian::where('siswa_id', $rekap->siswa_id)
                ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)
                ->where('status', 'Alpha')->count();
            
            $sakit = AbsensiHarian::where('siswa_id', $rekap->siswa_id)
                ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)
                ->where('status', 'Sakit')->count();

            $izin = AbsensiHarian::where('siswa_id', $rekap->siswa_id)
                ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)
                ->where('status', 'Izin')->count();

            $hasil[] = [
                'siswa_id' => $siswa->id,
                'nama_siswa' => $siswa->nama_lengkap,
                'nisn' => $siswa->nisn,
                'total_tidak_hadir' => $rekap->total,
                'total_alpha' => $alpha,
                'total_sakit' => $sakit,
                'total_izin' => $izin,
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

        // Ambil list tanggal ketidakhadiran
        $detail = AbsensiHarian::where('siswa_id', $request->siswa_id)
            ->whereMonth('tanggal', $request->bulan)
            ->whereYear('tanggal', $request->tahun)
            ->whereIn('status', ['Alpha', 'Sakit', 'Izin'])
            ->orderBy('tanggal', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'tanggal' => date('d M Y', strtotime($item->tanggal)),
                    'status' => $item->status,
                    'keterangan' => $item->keterangan ?? '-',
                ];
            });

        return response()->json($detail);
    }
}