<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CetakKartuController extends Controller
{
    public function cetak(Request $request)
    {
        $ids = explode(',', $request->query('ids'));
        $students = Siswa::with(['kelas', 'sekolah'])->whereIn('id', $ids)->get();

        if ($students->isEmpty()) {
            return "Tidak ada siswa yang dipilih.";
        }

        $zipFileName = 'Kartu_Pelajar_' . date('Ymd_His') . '.zip';
        $zipFilePath = storage_path('app/public/' . $zipFileName);

        $zip = new ZipArchive;
        if ($zip->open($zipFilePath, ZipArchive::CREATE) === TRUE) {
            foreach ($students as $siswa) {
                // 1. Setup Kanvas Kartu (Portrait: 638x1011 px ~ 54x85mm @ 300dpi)
                // Kita gunakan ukuran lebih kecil agar file tidak terlalu besar, tapi cukup jelas
                $width = 600;
                $height = 950;
                $img = imagecreatetruecolor($width, $height);

                // Warna
                $white = imagecolorallocate($img, 255, 255, 255);
                $blue = imagecolorallocate($img, 37, 99, 235); // Header
                $orange = imagecolorallocate($img, 245, 158, 11); // Footer
                $black = imagecolorallocate($img, 30, 30, 30);
                $grey = imagecolorallocate($img, 100, 100, 100);

                // Fill Background Putih
                imagefill($img, 0, 0, $white);

                // Gambar Header Biru
                imagefilledrectangle($img, 0, 0, $width, 160, $blue);

                // Gambar Footer Orange
                imagefilledrectangle($img, 0, $height - 20, $width, $height, $orange);

                // 2. LOGO SEKOLAH
                $logoPath = null;
                if ($siswa->sekolah && $siswa->sekolah->logo) {
                    if (Storage::disk('uploads')->exists($siswa->sekolah->logo)) {
                        $logoPath = Storage::disk('uploads')->path($siswa->sekolah->logo);
                    } elseif (file_exists(public_path('uploads/' . $siswa->sekolah->logo))) {
                        $logoPath = public_path('uploads/' . $siswa->sekolah->logo);
                    }
                }
                // Fallback logo default
                if (!$logoPath && file_exists(public_path('images/default-logo.png'))) {
                    $logoPath = public_path('images/default-logo.png');
                }

                if ($logoPath) {
                    $logo = imagecreatefromstring(file_get_contents($logoPath));
                    if ($logo) {
                        // Resize logo ke 80x80
                        $logoW = 100; $logoH = 100;
                        imagecopyresampled($img, $logo, ($width/2) - ($logoW/2), 20, 0, 0, $logoW, $logoH, imagesx($logo), imagesy($logo));
                        imagedestroy($logo);
                    }
                }

                // 3. TEKS (Nama Sekolah & Siswa)
                // Helper function untuk text tengah
                $drawCentered = function($image, $size, $y, $color, $text) use ($width) {
                    // Gunakan font bawaan (1-5)
                    $fontWidth = imagefontwidth($size);
                    $textWidth = strlen($text) * $fontWidth;
                    $x = ($width - $textWidth) / 2;
                    imagestring($image, $size, $x, $y, $text, $color);
                };

                // Nama Sekolah (Header)
                $namaSekolah = Str::upper($siswa->sekolah->nama_sekolah ?? 'SEKOLAH');
                $drawCentered($img, 5, 130, $white, $namaSekolah);
                $drawCentered($img, 2, 148, $white, "NPSN: " . ($siswa->sekolah->npsn ?? '-'));

                // Nama Siswa (Body)
                $drawCentered($img, 5, 220, $black, Str::upper($siswa->nama_lengkap));
                
                // Info Lain
                $drawCentered($img, 4, 250, $grey, "NIS / NISN");
                $drawCentered($img, 5, 270, $black, ($siswa->nis ?? '-') . " / " . $siswa->nisn);
                
                // Kelas tidak ditampilkan sesuai request sebelumnya

                // 4. QR CODE (Di Bawah)
                $qrRaw = QrCode::format('png')
                            ->size(350)
                            ->margin(1)
                            ->errorCorrection('H')
                            ->generate($siswa->qr_code_data);
                
                $qrImage = imagecreatefromstring($qrRaw);
                
                // Tempel Logo di tengah QR (Optional: jika logo sekolah ada)
                if ($logoPath) {
                     $logoQr = imagecreatefromstring(file_get_contents($logoPath));
                     $qrW = imagesx($qrImage);
                     $logoTargetW = $qrW * 0.25;
                     $centerX = ($qrW - $logoTargetW) / 2;
                     // Kotak putih
                     $whiteC = imagecolorallocate($qrImage, 255, 255, 255);
                     imagefilledrectangle($qrImage, $centerX, $centerX, $centerX + $logoTargetW, $centerX + $logoTargetH = $logoTargetW, $whiteC);
                     // Tempel
                     imagecopyresampled($qrImage, $logoQr, $centerX, $centerX, 0, 0, $logoTargetW, $logoTargetH, imagesx($logoQr), imagesy($logoQr));
                }

                // Tempel QR ke Kartu
                $qrDestW = 350;
                $qrX = ($width - $qrDestW) / 2;
                $qrY = 400; // Posisi Y QR Code
                imagecopy($img, $qrImage, $qrX, $qrY, 0, 0, imagesx($qrImage), imagesy($qrImage));
                
                // Footer Text
                $drawCentered($img, 2, 910, $grey, "KARTU PRESENSI SISWA");

                // 5. Simpan ke ZIP
                ob_start();
                imagepng($img);
                $content = ob_get_contents();
                ob_end_clean();

                $fileName = $siswa->nisn . '_' . Str::slug($siswa->nama_lengkap) . '.png';
                $zip->addFromString($fileName, $content);

                imagedestroy($img);
                imagedestroy($qrImage);
            }
            $zip->close();
        }

        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }
}