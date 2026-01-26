<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Cetak Kartu Pelajar</title>
    <style>
        @page { 
            margin: 0; 
            size: A4 portrait;
        }
        body { 
            margin: 0; 
            font-family: 'Helvetica', sans-serif; 
            background: #ffffff;
        }
        
        .page-wrapper {
            width: 210mm;
            min-height: 297mm;
            /* Padding diatur agar muat 3 kartu ke samping dan 3 ke bawah */
            padding-top: 10mm;
            padding-left: 12mm; 
            padding-right: 5mm; 
            box-sizing: border-box;
            position: relative;
        }

        .card-container {
            width: 54mm; 
            height: 85.6mm; 
            background: #ffffff;
            border: 1px solid #9CA3AF; /* Border abu-abu tipis untuk panduan potong */
            float: left;
            /* Jarak antar kartu */
            margin-right: 5mm; 
            margin-bottom: 5mm;
            position: relative;
            border-radius: 8px;
            overflow: hidden;
        }
        
        /* Hapus margin kanan pada kartu ke-3 di setiap baris agar tidak turun */
        .card-container:nth-child(3n) {
            margin-right: 0;
        }

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
            margin-left: -6mm; /* Tengah manual: -1/2 lebar */
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
            bottom: 6mm; /* Jarak dari bawah */
        }

        .qr-img {
            width: 30mm;
            height: 30mm;
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

        /* Pemisah Halaman untuk Cetak */
        .page-break {
            page-break-after: always;
            clear: both;
            display: block;
            height: 1px;
        }
        
        .clearfix {
            clear: both;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
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
                    <div class="student-name">{{ Str::limit($siswa->nama_lengkap, 18) }}</div>
                    
                    <div class="info-label">NIS / NISN</div>
                    <div class="info-value">{{ $siswa->nis ?? '-' }} / {{ $siswa->nisn }}</div>
                </div>

                <!-- QR Code (Dengan Logo Tengah) -->
                <div class="qr-area">
                    @php
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

                                    $finalImg = imagecreatetruecolor($qrW, $qrH);
                                    $white = imagecolorallocate($finalImg, 255, 255, 255);
                                    imagefill($finalImg, 0, 0, $white);
                                    imagecopy($finalImg, $qrImage, 0, 0, 0, 0, $qrW, $qrH);

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

            {{-- Logic Page Break setiap 9 kartu (3x3) --}}
            @if (($index + 1) % 9 == 0 && ($index + 1) < count($students))
                <div class="page-break"></div>
                <!-- Buka container baru setelah break -->
                </div><div class="page-wrapper">
            @endif

        @endforeach
        
        <div class="clearfix"></div>
    </div>
</body>
</html>