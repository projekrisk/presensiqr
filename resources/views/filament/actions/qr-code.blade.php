<div class="flex flex-col items-center justify-center space-y-4 p-4 text-center">
    <div class="border-4 border-black p-2 bg-white inline-block">
        @php
            $qrData = $record->qr_code_data;
            
            // 1. Logika Pencarian Logo
            $logoPath = null;
            if ($record->sekolah && $record->sekolah->logo) {
                $filename = $record->sekolah->logo;
                // Cek lokasi file di storage atau public
                try {
                    if (\Illuminate\Support\Facades\Storage::disk('uploads')->exists($filename)) {
                        $logoPath = \Illuminate\Support\Facades\Storage::disk('uploads')->path($filename);
                    } elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists($filename)) {
                        $logoPath = \Illuminate\Support\Facades\Storage::disk('public')->path($filename);
                    }
                } catch (\Exception $e) {}
                
                // Fallback manual check
                if (!$logoPath) {
                    if (file_exists(public_path('uploads/' . $filename))) {
                        $logoPath = public_path('uploads/' . $filename);
                    }
                }
            }

            $src = '';
            
            // 2. Generate QR Code
            if ($logoPath && file_exists($logoPath)) {
                try {
                    // A. Generate QR Mentah (PNG) dengan Error Correction High
                    // Error 'H' (High) wajib agar QR tetap terbaca meski tengahnya ditutup
                    $baseQr = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                                ->size(300)
                                ->margin(1)
                                ->errorCorrection('H')
                                ->generate($qrData);

                    // B. Manipulasi Gambar dengan GD Library (Agar ada Background Putih)
                    $qrImage = imagecreatefromstring($baseQr);
                    $logoImage = imagecreatefromstring(file_get_contents($logoPath));
                    
                    if ($qrImage && $logoImage) {
                        $qrWidth = imagesx($qrImage);
                        $qrHeight = imagesy($qrImage);
                        $logoOriginalW = imagesx($logoImage);
                        $logoOriginalH = imagesy($logoImage);
                        
                        // Hitung ukuran logo (30% dari lebar QR)
                        $logoTargetW = $qrWidth * 0.30;
                        $scale = $logoOriginalW / $logoTargetW;
                        $logoTargetH = $logoOriginalH / $scale;
                        
                        // Hitung posisi tengah
                        $centerX = ($qrWidth - $logoTargetW) / 2;
                        $centerY = ($qrHeight - $logoTargetH) / 2;
                        
                        // [FITUR BARU] Buat Kotak Putih di Tengah (Menghapus titik QR)
                        $whiteColor = imagecolorallocate($qrImage, 255, 255, 255);
                        imagefilledrectangle($qrImage, $centerX, $centerY, $centerX + $logoTargetW, $centerY + $logoTargetH, $whiteColor);
                        
                        // Tempel Logo di atas area putih
                        imagecopyresampled($qrImage, $logoImage, $centerX, $centerY, 0, 0, $logoTargetW, $logoTargetH, $logoOriginalW, $logoOriginalH);
                        
                        // Render ke Base64
                        ob_start();
                        imagepng($qrImage);
                        $finalQrData = ob_get_contents();
                        ob_end_clean();
                        
                        $src = 'data:image/png;base64,' . base64_encode($finalQrData);
                        
                        // Bersihkan memori
                        imagedestroy($qrImage);
                        imagedestroy($logoImage);
                    } else {
                        // Fallback jika GD gagal
                        $src = 'data:image/png;base64,' . base64_encode($baseQr);
                    }
                } catch (\Exception $e) {
                    // Fallback ke SVG standar jika error
                    $svgData = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(300)->margin(1)->generate($qrData);
                    $src = 'data:image/svg+xml;base64,' . base64_encode($svgData);
                }
            } else {
                // Tidak ada logo -> Pakai SVG standar (Lebih tajam)
                $svgData = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(300)->margin(1)->generate($qrData);
                $src = 'data:image/svg+xml;base64,' . base64_encode($svgData);
            }
        @endphp

        <!-- Tampilkan Hasil -->
        <img src="{{ $src }}" alt="QR Code Siswa" class="max-w-full h-auto" style="width: 250px; height: 250px;">
    </div>
    
    <div>
        <h2 class="text-xl font-bold text-gray-800">{{ $record->nama_lengkap }}</h2>
        <p class="text-sm text-gray-500 font-mono">NISN: {{ $record->nisn }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $record->kelas->nama_kelas ?? '-' }}</p>
        <!-- Tampilkan Nama Sekolah -->
        <p class="text-xs text-gray-800 mt-1 font-bold">{{ $record->sekolah->nama_sekolah ?? '' }}</p>
    </div>

    <div class="pt-4 print:hidden">
        <p class="text-xs text-gray-400 mb-2">Klik kanan pada gambar untuk menyimpan.</p>
    </div>
</div>