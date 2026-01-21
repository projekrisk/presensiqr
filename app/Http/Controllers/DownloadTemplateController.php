<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel; // Pastikan import ini

class DownloadTemplateController extends Controller
{
    public function downloadTemplateSiswa()
    {
        // Kita buat data dummy untuk contoh
        $data = [
            ['Nama Lengkap', 'Jenis Kelamin (L/P)', 'NISN', 'NIS', 'Nama Kelas'], // Header
            ['Budi Santoso', 'L', '1234567890', '1001', 'X RPL 1'], // Contoh 1
            ['Siti Aminah', 'P', '0987654321', '1002', 'XI TKJ 2'], // Contoh 2
        ];

        // Export array ke Excel (Cara cepat tanpa bikin class Export terpisah)
        return Excel::download(new class($data) implements \Maatwebsite\Excel\Concerns\FromArray {
            protected $data;
            public function __construct($data) { $this->data = $data; }
            public function array(): array { return $this->data; }
        }, 'template_siswa.xlsx');
    }
}
