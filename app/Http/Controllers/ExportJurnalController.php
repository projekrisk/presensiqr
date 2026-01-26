<?php

namespace App\Http\Controllers;

use App\Exports\JurnalAbsensiExport;
use App\Exports\JurnalBulananExport;
use App\Models\JurnalGuru;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ExportJurnalController extends Controller
{
    // Export Harian (Per Jurnal ID)
    public function export($id)
    {
        $jurnal = JurnalGuru::with('kelas')->findOrFail($id);
        
        // FIX: Pastikan tanggal diparse dengan Carbon
        $tglObj = $jurnal->tanggal instanceof Carbon ? $jurnal->tanggal : Carbon::parse($jurnal->tanggal);
        $tanggalStr = $tglObj->format('Y-m-d');
        
        $namaKelas = Str::slug($jurnal->kelas->nama_kelas);
        $fileName = "Absensi_{$namaKelas}_{$tanggalStr}.xlsx";

        return Excel::download(new JurnalAbsensiExport($id), $fileName);
    }

    // Export Bulanan
    public function exportBulanan(Request $request)
    {
        $bulan = $request->query('bulan', date('m'));
        $tahun = $request->query('tahun', date('Y'));
        
        $fileName = "Rekap_Absensi_Bulan_{$bulan}_{$tahun}.xlsx";
        
        return Excel::download(new JurnalBulananExport($bulan, $tahun), $fileName);
    }
}