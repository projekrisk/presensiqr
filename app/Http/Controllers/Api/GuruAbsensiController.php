<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbsensiHarian;
use App\Models\JurnalGuru;
use App\Models\DetailJurnal;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuruAbsensiController extends Controller
{
    /**
     * Cek Riwayat Absensi (GET)
     */
    public function check(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'tanggal' => 'required|date',
            'kelas' => 'required|string',
        ]);

        $tanggal = $request->query('tanggal');
        $namaKelas = $request->query('kelas');

        $kelas = Kelas::where('nama_kelas', $namaKelas)
                      ->where('sekolah_id', $user->sekolah_id)
                      ->first();

        if (!$kelas) {
            return response()->json([]);
        }

        // Cari data absensi dari jurnal yang sudah ada
        // Logika: Cari jurnal guru pada tanggal & kelas tsb, lalu ambil detailnya
        $jurnal = JurnalGuru::where('kelas_id', $kelas->id)
                    ->where('tanggal', $tanggal)
                    ->where('user_id', $user->id)
                    ->with('detail')
                    ->first();

        if (!$jurnal) {
            return response()->json([]);
        }

        // Format data agar sesuai dengan model AbsensiItem di Android
        $data = $jurnal->detail->map(function($detail) use ($jurnal) {
            return [
                'siswa_id' => $detail->siswa_id,
                'tanggal' => $jurnal->tanggal->format('Y-m-d'),
                'jam_masuk' => $jurnal->jam_ke ?? '00:00', // Gunakan jam_ke atau default
                'status' => $detail->status,
                'sekolah_id' => $jurnal->sekolah_id,
            ];
        });

        return response()->json($data);
    }

    /**
     * Simpan Absensi Kelas ke Jurnal Guru (POST)
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'tanggal'  => 'required|date',
            'details'  => 'required|array',
            'details.*.siswa_id' => 'required|exists:siswa,id',
            'details.*.status'   => 'required|in:Hadir,Sakit,Izin,Alpha',
        ]);

        // Cek apakah jurnal untuk kelas & tanggal ini sudah ada?
        $jurnal = JurnalGuru::where('user_id', $user->id)
            ->where('kelas_id', $request->kelas_id)
            ->where('tanggal', $request->tanggal)
            ->first();

        DB::beginTransaction();
        try {
            if (!$jurnal) {
                // Buat Jurnal Baru
                $jurnal = JurnalGuru::create([
                    'sekolah_id'     => $user->sekolah_id,
                    'user_id'        => $user->id,
                    'kelas_id'       => $request->kelas_id,
                    'tanggal'        => $request->tanggal,
                    'mata_pelajaran' => 'Wali Kelas / Umum', // Default
                    'jam_ke'         => date('H:i'),
                ]);
            }

            // Hapus detail lama agar tidak duplikat (reset status hari itu)
            DetailJurnal::where('jurnal_guru_id', $jurnal->id)->delete();

            // Insert data baru
            $insertData = [];
            foreach ($request->details as $item) {
                $insertData[] = [
                    'jurnal_guru_id' => $jurnal->id,
                    'siswa_id'       => $item['siswa_id'],
                    'status'         => $item['status'],
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];
            }
            DetailJurnal::insert($insertData);

            DB::commit();
            return response()->json(['message' => 'Jurnal berhasil disimpan', 'id' => $jurnal->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
        }
    }
}
