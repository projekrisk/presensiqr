<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JurnalGuru;
use App\Models\DetailJurnal;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; // Import Carbon

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

        // Gunakan Carbon untuk memastikan format tanggal Y-m-d
        $tanggal = Carbon::parse($request->query('tanggal'))->format('Y-m-d');
        $namaKelas = $request->query('kelas');

        $kelas = Kelas::where('nama_kelas', $namaKelas)
                      ->where('sekolah_id', $user->sekolah_id)
                      ->first();

        if (!$kelas) {
            return response()->json([]);
        }

        // Cari data absensi
        $jurnal = JurnalGuru::where('kelas_id', $kelas->id)
                    ->whereDate('tanggal', $tanggal) // Gunakan whereDate agar lebih aman
                    ->where('user_id', $user->id)
                    ->with('detail')
                    ->first();

        if (!$jurnal) {
            return response()->json([]);
        }

        $data = $jurnal->detail->map(function($detail) use ($jurnal) {
            return [
                'siswa_id' => $detail->siswa_id,
                'tanggal' => Carbon::parse($jurnal->tanggal)->format('Y-m-d'),
                'jam_masuk' => '00:00', 
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

        $jurnal = JurnalGuru::where('user_id', $user->id)
            ->where('kelas_id', $request->kelas_id)
            ->whereDate('tanggal', $request->tanggal)
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
                ]);
            }

            // Hapus detail lama agar tidak duplikat
            DetailJurnal::where('jurnal_guru_id', $jurnal->id)->delete();

            // Insert data baru & Hitung Rekap
            $insertData = [];
            $rekap = ['Hadir' => 0, 'Sakit' => 0, 'Izin' => 0, 'Alpha' => 0];

            foreach ($request->details as $item) {
                $status = $item['status'];
                if (isset($rekap[$status])) {
                    $rekap[$status]++;
                }

                $insertData[] = [
                    'jurnal_guru_id' => $jurnal->id,
                    'siswa_id'       => $item['siswa_id'],
                    'status'         => $status,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];
            }
            DetailJurnal::insert($insertData);

            // Update Rekap Jumlah
            $jurnal->update([
                'hadir' => $rekap['Hadir'],
                'sakit' => $rekap['Sakit'],
                'izin'  => $rekap['Izin'],
                'alpha' => $rekap['Alpha'],
            ]);

            DB::commit();
            return response()->json(['message' => 'Jurnal berhasil disimpan', 'id' => $jurnal->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }
}