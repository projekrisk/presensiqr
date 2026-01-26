<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class CetakKartuController extends Controller
{
    public function cetak(Request $request)
    {
        // Ambil ID dari parameter query string (format: 1,2,3)
        $ids = explode(',', $request->query('ids'));
        
        // Ambil data siswa beserta relasi sekolah dan kelas
        $students = Siswa::with(['kelas', 'sekolah'])
            ->whereIn('id', $ids)
            ->get();

        if ($students->isEmpty()) {
            return "Tidak ada siswa yang dipilih.";
        }

        // Setup PDF (Landscape A4 atau Portrait A4 berisi banyak kartu)
        $pdf = Pdf::loadView('pdf.kartu-pelajar', compact('students'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Kartu_Pelajar.pdf');
    }
}
