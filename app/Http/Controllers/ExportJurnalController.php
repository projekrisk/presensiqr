<?php

namespace App\Http\Controllers;

use App\Exports\JurnalAbsensiExport;
use App\Models\JurnalGuru;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class ExportJurnalController extends Controller
{
    public function export($id)
    {
        $jurnal = JurnalGuru::with('kelas')->findOrFail($id);
        
        // Buat nama file: Absensi_X-RPL-1_2024-01-01.xlsx
        $namaKelas = Str::slug($jurnal->kelas->nama_kelas);
        $tanggal = $jurnal->tanggal->format('Y-m-d');
        $fileName = "Absensi_{$namaKelas}_{$tanggal}.xlsx";

        return Excel::download(new JurnalAbsensiExport($id), $fileName);
    }
}