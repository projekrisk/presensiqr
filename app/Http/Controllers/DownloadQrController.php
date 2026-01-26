<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DownloadQrController extends Controller
{
    public function download(Request $request)
    {
        $ids = explode(',', $request->query('ids'));
        $students = Siswa::with('kelas')->whereIn('id', $ids)->get();

        if ($students->isEmpty()) {
            return "Tidak ada siswa yang dipilih.";
        }

        // Nama file ZIP
        $zipFileName = 'QR_Codes_' . date('Ymd_His') . '.zip';
        $zipFilePath = storage_path('app/public/' . $zipFileName);

        $zip = new ZipArchive;
        if ($zip->open($zipFilePath, ZipArchive::CREATE) === TRUE) {
            foreach ($students as $siswa) {
                // Generate QR Code PNG (Hanya QR hitam putih untuk kejelasan scan)
                $qrContent = QrCode::format('png')
                    ->size(500)
                    ->margin(1)
                    ->errorCorrection('H')
                    ->generate($siswa->qr_code_data);

                // Buat nama file yang aman: KELAS_NAMA_NISN.png
                $kelas = $siswa->kelas ? Str::slug($siswa->kelas->nama_kelas) : 'TanpaKelas';
                $nama = Str::slug($siswa->nama_lengkap);
                $fileName = "{$kelas}_{$nama}_{$siswa->nisn}.png";

                $zip->addFromString($fileName, $qrContent);
            }
            $zip->close();
        }

        // Download lalu hapus file ZIP dari server
        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }
}
