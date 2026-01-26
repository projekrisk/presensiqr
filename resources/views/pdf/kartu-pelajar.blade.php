<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Cetak Kartu Pelajar</title>
    <style>
        @page { margin: 0; size: A4; }
        body { margin: 0; font-family: 'Helvetica', sans-serif; background: #f0f0f0; }
        
        .page-container {
            width: 210mm;
            min-height: 297mm;
            background: white;
            padding: 10mm;
            margin: 0 auto;
            box-sizing: border-box;
            overflow: hidden;
        }

        .card-container {
            width: 54mm; /* Portrait Width */
            height: 85.6mm; /* Portrait Height */
            background: white;
            border: 1px solid #e5e7eb;
            float: left;
            margin: 0 5mm 5mm 0;
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            page-break-inside: avoid;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
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

        /* Logo Sekolah (Tengah Atas) */
        .school-logo {
            position: absolute;
            top: 2mm;
            left: 50%;
            transform: translateX(-50%); /* Center horizontal - but DOMPDF has issues with transform, use margin */
            margin-left: -6mm; /* Half of width */
            width: 12mm;
            height: 12mm;
            z-index: 10;
            object-fit: contain;
        }

        /* Nama Sekolah & NPSN (Di bawah Header) */
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

        /* QR Code Besar di Bawah */
        .qr-area {
            width: 100%;
            text-align: center;
            position: absolute;
            bottom: 6mm;
        }

        .qr-img {
            width: 32mm; /* Besar dan Jelas */
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
    </style>
</head>
<body>
    <div class="page-container">
        @foreach($students as $siswa)
            <div class="card-container">
                <!-- Header Background -->
                <div class="card-header"></div>
                
                <!-- Logo Sekolah (Centered Manual via Margin) -->
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

                    <div class="info-label">KELAS</div>
                    <div class="info-value">{{ $siswa->kelas->nama_kelas }}</div>
                </div>

                <!-- QR Code -->
                <div class="qr-area">
                    <img src="data:image/png;base64, {{ base64_encode(SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(300)->margin(0)->generate($siswa->qr_code_data)) }}" class="qr-img">
                </div>

                <div class="footer-strip"></div>
            </div>
        @endforeach
    </div>
</body>
</html>