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
        $students = Siswa::with(['kelas', 'sekolah'])->whereIn('id', $ids)->get();

        if ($students->isEmpty()) {
            return "Tidak ada siswa yang dipilih.";
        }

        // Nama file ZIP
        $zipFileName = 'QR_Codes_' . date('Ymd_His') . '.zip';
        $zipFilePath = storage_path('app/public/' . $zipFileName);

        $zip = new ZipArchive;
        if ($zip->open($zipFilePath, ZipArchive::CREATE) === TRUE) {
            foreach ($students as $siswa) {
                // 1. Cek Logo Sekolah
                $logoPath = null;
                if ($siswa->sekolah && $siswa->sekolah->logo) {
                    // Cek di disk 'uploads' dulu
                    if (Storage::disk('uploads')->exists($siswa->sekolah->logo)) {
                        $logoPath = Storage::disk('uploads')->path($siswa->sekolah->logo);
                    } 
                    // Cek fallback di public path
                    elseif (file_exists(public_path('uploads/' . $siswa->sekolah->logo))) {
                        $logoPath = public_path('uploads/' . $siswa->sekolah->logo);
                    }
                }

                $qrContent = null;

                // 2. Setup Generator
                $qrGenerator = QrCode::format('png')
                    ->size(500)
                    ->margin(1)
                    ->errorCorrection('H'); // High Error Correction wajib

                // 3. Generate dengan Manipulasi Gambar (GD Library)
                if ($logoPath && file_exists($logoPath)) {
                    try {
                        // A. Generate QR Dasar
                        $baseQr = $qrGenerator->generate($siswa->qr_code_data);

                        // B. Manipulasi Gambar: Tambah Kotak Putih + Logo
                        $qrImage = imagecreatefromstring($baseQr);
                        $logoImage = imagecreatefromstring(file_get_contents($logoPath));

                        if ($qrImage && $logoImage) {
                            $qrWidth = imagesx($qrImage);
                            $qrHeight = imagesy($qrImage);
                            $logoOriginalW = imagesx($logoImage);
                            $logoOriginalH = imagesy($logoImage);

                            // Hitung ukuran logo (30% dari QR)
                            $logoTargetW = $qrWidth * 0.30;
                            $scale = $logoOriginalW / $logoTargetW;
                            $logoTargetH = $logoOriginalH / $scale;

                            // Posisi Tengah
                            $centerX = ($qrWidth - $logoTargetW) / 2;
                            $centerY = ($qrHeight - $logoTargetH) / 2;

                            // Buat Kotak Putih (Background Logo)
                            $whiteColor = imagecolorallocate($qrImage, 255, 255, 255);
                            imagefilledrectangle(
                                $qrImage, 
                                $centerX, $centerY, 
                                $centerX + $logoTargetW, $centerY + $logoTargetH, 
                                $whiteColor
                            );

                            // Tempel Logo
                            imagecopyresampled(
                                $qrImage, $logoImage, 
                                $centerX, $centerY, 
                                0, 0, 
                                $logoTargetW, $logoTargetH, 
                                $logoOriginalW, $logoOriginalH
                            );

                            // Simpan hasil ke variable
                            ob_start();
                            imagepng($qrImage);
                            $qrContent = ob_get_contents();
                            ob_end_clean();

                            // Bersihkan memori
                            imagedestroy($qrImage);
                            imagedestroy($logoImage);
                        } else {
                            // Fallback jika gagal baca gambar: Timpa langsung (tanpa bg putih)
                            $qrContent = $qrGenerator->merge($logoPath, 0.3, true)->generate($siswa->qr_code_data);
                        }
                    } catch (\Exception $e) {
                        // Fallback ke QR Polos jika error
                        $qrContent = $qrGenerator->generate($siswa->qr_code_data);
                    }
                } else {
                    // Tidak ada logo -> QR Polos
                    $qrContent = $qrGenerator->generate($siswa->qr_code_data);
                }

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