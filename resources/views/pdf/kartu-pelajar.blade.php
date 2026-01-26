<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Cetak Kartu Pelajar</title>
    <style>
        @page { 
            margin: 0; 
            size: A4 portrait; /* Wajib Portrait */
        }
        body { 
            margin: 0; 
            font-family: 'Helvetica', sans-serif; 
            background: #e5e7eb; 
            -webkit-print-color-adjust: exact; 
        }
        
        .page-container {
            width: 210mm;
            min-height: 297mm;
            background: white;
            /* Padding disesuaikan agar muat 3x3 dengan margin kartu */
            padding-top: 10mm;
            padding-left: 12mm; 
            padding-right: 10mm;
            box-sizing: border-box;
            overflow: hidden;
        }

        .card-container {
            width: 54mm; 
            height: 85.6mm; 
            background: #ffffff;
            border: 1px solid #d1d5db;
            float: left;
            /* Jarak antar kartu */
            margin-right: 5mm; 
            margin-bottom: 5mm;
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            page-break-inside: avoid;
        }
        
        /* Hilangkan margin kanan untuk kartu ke-3, ke-6, dst di setiap baris (opsional, tapi float biasanya handle ini) */
        /* .card-container:nth-child(3n) { margin-right: 0; } */

        /* Header Biru */
        .card-header {
            height: 15mm;
            background: #2563EB;
            width: 100%;
            position: absolute;
            top: 0;
            left: 0;
            text-align: center;
        }

        /* Logo Sekolah */
        .school-logo {
            position: absolute;
            top: 2mm;
            left: 50%;
            transform: translateX(-50%);
            margin-left: -6mm; 
            width: 12mm;
            height: 12mm;
            z-index: 10;
            object-fit: contain;
        }

        /* Nama Sekolah & NPSN */
        .header-text {
            margin-top: 16mm;
            text-align: center;
            width: 100%;
            padding: 0 2mm;
        }

        .school-name {
            color: #1E40AF;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .school-npsn {
            color: #6B7280;
            font-size: 6pt;
            margin-top: 1px;
        }

        /* Konten Utama */
        .content-area {
            width: 100%;
            text-align: center;
            margin-top: 4mm;
        }

        .student-name {
            font-size: 11pt;
            font-weight: 800;
            color: #111827;
            text-transform: uppercase;
            margin-bottom: 2mm;
            padding: 0 2mm;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .info-label {
            font-size: 6pt;
            color: #9CA3AF;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 9pt;
            font-weight: bold;
            color: #374151;
            margin-bottom: 3mm;
        }

        /* QR Code */
        .qr-area {
            width: 100%;
            text-align: center;
            position: absolute;
            bottom: 6mm;
        }

        .qr-img {
            width: 32mm;
            height: 32mm;
        }

        /* Footer Strip */
        .footer-strip {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2mm;
            background: #F59E0B;
        }

        /* Pemisah Halaman */
        .page-break {
            page-break-after: always;
            clear: both;
            display: block;
            height: 0;
            visibility: hidden;
        }
    </style>
</head>
<body>
    <div class="page-container">
        @foreach($students as $index => $siswa)
            <div class="card-container">
                <!-- Header Background -->
                <div class="card-header"></div>
                
                <!-- Logo Sekolah -->
                @php
                    $logoPath = public_path('images/default-logo.png');
                    if($siswa->sekolah->logo) {
                        if(\Illuminate\Support\Facades\Storage::disk('uploads')->exists($siswa->sekolah->logo)) {
                            $logoPath = \Illuminate\Support\Facades\Storage::disk('uploads')->path($siswa->sekolah->logo);
                        } elseif(file_exists(public_path('uploads/'.$siswa->sekolah->logo))) {
                            $logoPath = public_path('uploads/'.$siswa->sekolah->logo);
                        }
                    }
                    $logoBase64 = '';
                    if(file_exists($logoPath)) {
                        $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
                    }
                @endphp
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" class="school-logo">
                @endif
                
                <!-- Header Text -->
                <div class="header-text">
                    <div class="school-name">{{ Str::limit($siswa->sekolah->nama_sekolah, 25) }}</div>
                    <div class="school-npsn">NPSN: {{ $siswa->sekolah->npsn ?? '-' }}</div>
                </div>

                <!-- Konten Siswa -->
                <div class="content-area">
                    <div class="student-name">{{ $siswa->nama_lengkap }}</div>
                    
                    <div class="info-label">NIS / NISN</div>
                    <div class="info-value">{{ $siswa->nis ?? '-' }} / {{ $siswa->nisn }}</div>

                    <!-- KELAS DIHAPUS SESUAI REQUEST SEBELUMNYA -->
                </div>

                <!-- QR Code (Dengan Logo Tengah) -->
                <div class="qr-area">
                    @php
                        // Logika QR dengan Logo Tengah (Sama seperti DownloadQrController)
                        $qrRaw = SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                                    ->size(300)
                                    ->margin(0)
                                    ->errorCorrection('H')
                                    ->generate($siswa->qr_code_data);
                        
                        $qrFinal = base64_encode($qrRaw);

                        if ($logoPath && function_exists('imagecreatefromstring')) {
                             try {
                                $qrImage = imagecreatefromstring($qrRaw);
                                $logoImage = imagecreatefromstring(file_get_contents($logoPath));

                                if ($qrImage && $logoImage) {
                                    $qrW = imagesx($qrImage); $qrH = imagesy($qrImage);
                                    $logoW = imagesx($logoImage); $logoH = imagesy($logoImage);

                                    // TrueColor Canvas
                                    $finalImg = imagecreatetruecolor($qrW, $qrH);
                                    $white = imagecolorallocate($finalImg, 255, 255, 255);
                                    imagefill($finalImg, 0, 0, $white);
                                    imagecopy($finalImg, $qrImage, 0, 0, 0, 0, $qrW, $qrH);

                                    // Logo 25%
                                    $logoTargetW = $qrW * 0.25;
                                    $scale = $logoW / $logoTargetW;
                                    $logoTargetH = $logoH / $scale;
                                    $centerX = ($qrW - $logoTargetW) / 2;
                                    $centerY = ($qrH - $logoTargetH) / 2;

                                    imagefilledrectangle($finalImg, $centerX, $centerY, $centerX + $logoTargetW, $centerY + $logoTargetH, $white);
                                    imagecopyresampled($finalImg, $logoImage, $centerX, $centerY, 0, 0, $logoTargetW, $logoTargetH, $logoW, $logoH);

                                    ob_start();
                                    imagepng($finalImg);
                                    $qrFinal = base64_encode(ob_get_contents());
                                    ob_end_clean();
                                }
                             } catch (\Exception $e) {}
                        }
                    @endphp
                    <img src="data:image/png;base64, {{ $qrFinal }}" class="qr-img">
                </div>

                <div class="footer-strip"></div>
            </div>

            {{-- Logic Page Break setiap 9 kartu --}}
            @if (($index + 1) % 9 == 0 && ($index + 1) < count($students))
                <div class="page-break"></div>
            @endif

        @endforeach
    </div>
</body>
</html>