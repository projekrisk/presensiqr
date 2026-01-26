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
            width: 85.6mm;
            height: 54mm;
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
            height: 14mm;
            background: #2563EB; /* Biru Utama */
            width: 100%;
            position: absolute;
            top: 0;
            left: 0;
            display: flex;
            align-items: center;
        }

        .school-logo {
            position: absolute;
            top: 2mm;
            left: 3mm;
            width: 10mm;
            height: 10mm;
            background: white;
            border-radius: 50%;
            padding: 1px;
            z-index: 10;
            object-fit: contain;
        }

        .school-name {
            position: absolute;
            top: 0;
            left: 15mm;
            right: 2mm;
            height: 14mm;
            display: table; /* Hack vertical align di PDF lama */
        }
        
        .school-name span {
            display: table-cell;
            vertical-align: middle;
            color: white;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Konten Utama: Bagi 2 Kolom */
        .content-area {
            position: absolute;
            top: 16mm;
            left: 0;
            width: 100%;
            height: 38mm;
            padding: 0 4mm;
            box-sizing: border-box;
        }

        /* Kolom Kiri: Info Siswa */
        .info-col {
            float: left;
            width: 60%;
            padding-top: 4mm;
        }

        .label {
            font-size: 6pt;
            color: #6B7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1px;
        }

        .value {
            font-size: 10pt;
            font-weight: bold;
            color: #111827;
            margin-bottom: 6px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .value-large {
            font-size: 11pt;
            font-weight: 800;
            color: #111827;
            margin-bottom: 6px;
            line-height: 1.1;
        }

        /* Kolom Kanan: QR Code Besar */
        .qr-col {
            float: right;
            width: 38%;
            text-align: right;
            padding-top: 1mm;
        }

        .qr-img {
            width: 30mm; /* Diperbesar */
            height: 30mm;
            border: 1px solid #f3f4f6;
            padding: 1px;
            border-radius: 4px;
        }

        /* Footer Strip */
        .footer-strip {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 1.5mm;
            background: #F59E0B; /* Aksen Kuning/Orange */
        }
    </style>
</head>
<body>
    <div class="page-container">
        @foreach($students as $siswa)
            <div class="card-container">
                <!-- Header -->
                <div class="card-header"></div>
                
                <!-- Logo -->
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
                
                <div class="school-name">
                    <span>{{ Str::limit($siswa->sekolah->nama_sekolah, 35) }}</span>
                </div>

                <!-- Konten -->
                <div class="content-area">
                    <!-- Info Kiri -->
                    <div class="info-col">
                        <div class="label">Nama Siswa</div>
                        <div class="value-large">{{ Str::limit($siswa->nama_lengkap, 20) }}</div>

                        <div class="label">NISN / NIS</div>
                        <div class="value">{{ $siswa->nisn }}</div>

                        <div class="label">Kelas</div>
                        <div class="value">{{ $siswa->kelas->nama_kelas }}</div>
                    </div>

                    <!-- QR Kanan -->
                    <div class="qr-col">
                        <!-- Generate QR (Tanpa Logo di tengah agar scan lebih cepat) -->
                        <img src="data:image/png;base64, {{ base64_encode(SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(200)->margin(0)->generate($siswa->qr_code_data)) }}" class="qr-img">
                    </div>
                </div>

                <div class="footer-strip"></div>
            </div>
        @endforeach
    </div>
</body>
</html>