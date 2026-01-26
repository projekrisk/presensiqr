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

        if ($students->isEmpty()) return "Tidak ada siswa dipilih.";

        $zipFileName = 'Kartu_Pelajar_' . date('Ymd_His') . '.zip';
        $zipFilePath = storage_path('app/public/' . $zipFileName);
        
        // Font Path
        $fontRegular = public_path('fonts/arial.ttf');
        $fontBold = public_path('fonts/arialbd.ttf');
        // Fallback jika font tidak ada
        $useTTF = file_exists($fontRegular) && file_exists($fontBold);

        $zip = new ZipArchive;
        if ($zip->open($zipFilePath, ZipArchive::CREATE) === TRUE) {
            foreach ($students as $siswa) {
                // Setup Kanvas (Portrait 638x1011 px ~ 54x85mm @ 300dpi)
                $width = 638;
                $height = 1011;
                $img = imagecreatetruecolor($width, $height);

                // Warna
                $white = imagecolorallocate($img, 255, 255, 255);
                $blue = imagecolorallocate($img, 37, 99, 235); // #2563EB
                $orange = imagecolorallocate($img, 245, 158, 11); // #F59E0B
                $black = imagecolorallocate($img, 17, 24, 39); // #111827
                $grey = imagecolorallocate($img, 107, 114, 128); // #6B7280

                // Background Putih
                imagefill($img, 0, 0, $white);

                // Header Biru (Tinggi 180px)
                imagefilledrectangle($img, 0, 0, $width, 180, $blue);

                // Footer Orange (Tinggi 20px)
                imagefilledrectangle($img, 0, $height - 20, $width, $height, $orange);

                // 1. LOGO SEKOLAH
                $logoPath = null;
                if ($siswa->sekolah && $siswa->sekolah->logo) {
                    if (Storage::disk('uploads')->exists($siswa->sekolah->logo)) $logoPath = Storage::disk('uploads')->path($siswa->sekolah->logo);
                    elseif (file_exists(public_path('uploads/' . $siswa->sekolah->logo))) $logoPath = public_path('uploads/' . $siswa->sekolah->logo);
                }
                if (!$logoPath && file_exists(public_path('images/default-logo.png'))) $logoPath = public_path('images/default-logo.png');

                if ($logoPath) {
                    $logo = imagecreatefromstring(file_get_contents($logoPath));
                    if ($logo) {
                        $logoSize = 130;
                        // Tempel di tengah atas (y=25)
                        imagecopyresampled($img, $logo, ($width - $logoSize)/2, 25, 0, 0, $logoSize, $logoSize, imagesx($logo), imagesy($logo));
                        imagedestroy($logo);
                    }
                }

                // 2. TEKS HEADER (Nama Sekolah & NPSN)
                $namaSekolah = Str::upper($siswa->sekolah->nama_sekolah ?? 'SEKOLAH');
                $npsn = "NPSN: " . ($siswa->sekolah->npsn ?? '-');

                if ($useTTF) {
                    // Nama Sekolah (Bold, Size 18)
                    $bbox = imagettfbbox(18, 0, $fontBold, $namaSekolah);
                    $x = ($width - ($bbox[2] - $bbox[0])) / 2;
                    imagettftext($img, 18, 0, $x, 220, $black, $fontBold, $namaSekolah); // Y=220 (bawah header biru)
                    
                    // NPSN (Regular, Size 12)
                    $bbox = imagettfbbox(12, 0, $fontRegular, $npsn);
                    $x = ($width - ($bbox[2] - $bbox[0])) / 2;
                    imagettftext($img, 12, 0, $x, 245, $grey, $fontRegular, $npsn);

                    // 3. IDENTITAS SISWA
                    // Nama Siswa (Bold, Besar)
                    $namaSiswa = Str::upper($siswa->nama_lengkap);
                    $bbox = imagettfbbox(22, 0, $fontBold, $namaSiswa);
                    $x = ($width - ($bbox[2] - $bbox[0])) / 2;
                    imagettftext($img, 22, 0, $x, 320, $black, $fontBold, $namaSiswa);

                    // Garis Pemisah
                    imagefilledrectangle($img, ($width/2)-50, 340, ($width/2)+50, 342, $orange);

                    // NIS / NISN
                    $labelNis = "NIS / NISN";
                    $valNis = ($siswa->nis ?? '-') . " / " . $siswa->nisn;
                    
                    $bbox = imagettfbbox(10, 0, $fontRegular, $labelNis);
                    $x = ($width - ($bbox[2] - $bbox[0])) / 2;
                    imagettftext($img, 10, 0, $x, 380, $grey, $fontRegular, $labelNis);

                    $bbox = imagettfbbox(16, 0, $fontBold, $valNis);
                    $x = ($width - ($bbox[2] - $bbox[0])) / 2;
                    imagettftext($img, 16, 0, $x, 410, $black, $fontBold, $valNis);

                } else {
                    // Fallback Font Bawaan (Jika TTF tidak ada)
                    // (Logika imagestring sederhana, kurang rapi tapi fungsional)
                    $drawCentered = function($img, $font, $y, $text, $color) use ($width) {
                        $x = ($width - (strlen($text) * imagefontwidth($font))) / 2;
                        imagestring($img, $font, $x, $y, $text, $color);
                    };
                    $drawCentered($img, 5, 200, $namaSekolah, $black);
                    $drawCentered($img, 5, 250, $siswa->nama_lengkap, $black);
                    $drawCentered($img, 4, 300, $valNis ?? $siswa->nisn, $black);
                }

                // 4. QR CODE (Di Bawah, Besar)
                $qrRaw = QrCode::format('png')->size(350)->margin(1)->errorCorrection('H')->generate($siswa->qr_code_data);
                $qrImage = imagecreatefromstring($qrRaw);
                
                // Tempel Logo di QR (Jika ada)
                if ($logoPath) {
                     $logoQr = imagecreatefromstring(file_get_contents($logoPath));
                     $qrW = imagesx($qrImage);
                     $logoTargetW = $qrW * 0.22; // 22%
                     $scale = imagesx($logoQr) / $logoTargetW;
                     $logoTargetH = imagesy($logoQr) / $scale;
                     $centerX = ($qrW - $logoTargetW) / 2;
                     $centerY = ($qrW - $logoTargetH) / 2;

                     $whiteC = imagecolorallocate($qrImage, 255, 255, 255);
                     imagefilledrectangle($qrImage, $centerX, $centerY, $centerX + $logoTargetW, $centerY + $logoTargetH, $whiteC);
                     imagecopyresampled($qrImage, $logoQr, $centerX, $centerY, 0, 0, $logoTargetW, $logoTargetH, imagesx($logoQr), imagesy($logoQr));
                }

                // Tempel QR ke Kartu (Posisi Y=480)
                $qrDestW = 350;
                $qrX = ($width - $qrDestW) / 2;
                imagecopy($img, $qrImage, $qrX, 480, 0, 0, imagesx($qrImage), imagesy($qrImage));

                // Footer Text
                if ($useTTF) {
                    $footer = "KARTU PRESENSI SISWA";
                    $bbox = imagettfbbox(8, 0, $fontRegular, $footer);
                    $x = ($width - ($bbox[2] - $bbox[0])) / 2;
                    imagettftext($img, 8, 0, $x, 930, $grey, $fontRegular, $footer);
                }

                // Simpan ke ZIP
                ob_start();
                imagepng($img);
                $content = ob_get_contents();
                ob_end_clean();

                $fileName = "KARTU_" . $siswa->nisn . '_' . Str::slug($siswa->nama_lengkap) . '.png';
                $zip->addFromString($fileName, $content);

                imagedestroy($img);
                imagedestroy($qrImage);
            }
            $zip->close();
        }

        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }
}