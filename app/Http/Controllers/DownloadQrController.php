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
        $students = Siswa::with(['sekolah'])->whereIn('id', $ids)->get();

        if ($students->isEmpty()) return "No data.";

        $zipFileName = 'QR_Only_' . date('Ymd_His') . '.zip';
        $zipFilePath = storage_path('app/public/' . $zipFileName);

        $zip = new ZipArchive;
        if ($zip->open($zipFilePath, ZipArchive::CREATE) === TRUE) {
            foreach ($students as $siswa) {
                // 1. Cek Logo
                $logoPath = null;
                if ($siswa->sekolah && $siswa->sekolah->logo) {
                    if (Storage::disk('uploads')->exists($siswa->sekolah->logo)) $logoPath = Storage::disk('uploads')->path($siswa->sekolah->logo);
                    elseif (file_exists(public_path('uploads/' . $siswa->sekolah->logo))) $logoPath = public_path('uploads/' . $siswa->sekolah->logo);
                }

                // 2. Generate QR
                $qrRaw = QrCode::format('png')->size(500)->margin(1)->errorCorrection('H')->generate($siswa->qr_code_data);
                
                // 3. Manipulasi (Putihkan tengah & Tempel Logo)
                if ($logoPath && function_exists('imagecreatefromstring')) {
                    try {
                        $qrImage = imagecreatefromstring($qrRaw);
                        $logoImage = imagecreatefromstring(file_get_contents($logoPath));

                        if ($qrImage && $logoImage) {
                            $qrW = imagesx($qrImage); $qrH = imagesy($qrImage);
                            
                            // Konversi ke TrueColor (Fix warna pucat)
                            $finalImg = imagecreatetruecolor($qrW, $qrH);
                            $white = imagecolorallocate($finalImg, 255, 255, 255);
                            imagefill($finalImg, 0, 0, $white);
                            imagecopy($finalImg, $qrImage, 0, 0, 0, 0, $qrW, $qrH);

                            // Logo 25%
                            $logoW = imagesx($logoImage); $logoH = imagesy($logoImage);
                            $logoTargetW = $qrW * 0.25;
                            $scale = $logoW / $logoTargetW;
                            $logoTargetH = $logoH / $scale;
                            $centerX = ($qrW - $logoTargetW) / 2;
                            $centerY = ($qrH - $logoTargetH) / 2;

                            imagefilledrectangle($finalImg, $centerX, $centerY, $centerX + $logoTargetW, $centerY + $logoTargetH, $white);
                            imagecopyresampled($finalImg, $logoImage, $centerX, $centerY, 0, 0, $logoTargetW, $logoTargetH, $logoW, $logoH);

                            ob_start();
                            imagepng($finalImg);
                            $qrRaw = ob_get_contents();
                            ob_end_clean();
                            
                            imagedestroy($finalImg);
                            imagedestroy($qrImage);
                            imagedestroy($logoImage);
                        }
                    } catch (\Exception $e) {}
                }

                $fileName = $siswa->nisn . '.png';
                $zip->addFromString($fileName, $qrRaw);
            }
            $zip->close();
        }

        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }
}