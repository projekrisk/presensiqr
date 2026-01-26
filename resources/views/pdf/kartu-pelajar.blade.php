<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Cetak Kartu Pelajar</title>
    <style>
        @page { margin: 0; size: A4; }
        body { margin: 0; font-family: 'Helvetica', sans-serif; background: #e5e7eb; -webkit-print-color-adjust: exact; }
        
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
            width: 54mm; /* Lebar ID Card Standar */
            height: 85.6mm; /* Tinggi ID Card Standar */
            background: #ffffff;
            border: 1px solid #d1d5db; /* Border tipis abu-abu */
            float: left;
            margin: 0 5mm 5mm 0;
            position: relative;
            border-radius: 8px; /* Sudut membulat */
            overflow: hidden;
            page-break-inside: avoid;
        }

        /* Background Dekorasi Atas (Biru Lengkung) */
        .bg-deco-top {
            position: absolute;
            top: -25mm;
            left: -10mm;
            width: 74mm;
            height: 55mm;
            background: #2563EB; /* Biru Utama */
            border-radius: 50%;
            z-index: 0;
        }
        
        /* Konten Kartu */
        .card-content {
            position: relative;
            z-index: 10;
            width: 100%;
            height: 100%;
            text-align: center;
        }

        /* Area Logo */
        .logo-area {
            margin-top: 5mm;
            height: 14mm;
            width: 100%;
            text-align: center;
        }
        
        .school-logo {
            width: 12mm;
            height: 12mm;
            object-fit: contain;
            background: white;
            border-radius: 50%;
            padding: 2px;
        }

        /* Info Sekolah */
        .school-info {
            color: white;
            margin-bottom: 6mm;
            padding: 0 2mm;
        }
        .school-name {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .school-npsn {
            font-size: 6pt;
            opacity: 0.9;
        }

        /* Info Siswa */
        .student-area {
            margin-top: 4mm;
            padding: 0 3mm;
        }
        
        .student-name {
            font-size: 11pt;
            font-weight: 800;
            color: #111827;
            text-transform: uppercase;
            margin-bottom: 2mm;
            line-height: 1.1;
            /* Pastikan nama panjang tidak menabrak */
            max-height: 10mm; 
            overflow: hidden;
        }
        
        /* Garis Pemisah Kecil */
        .divider {
            height: 2px;
            width: 15mm;
            background: #F59E0B; /* Orange */
            margin: 0 auto 3mm auto;
            border-radius: 1px;
        }

        .meta-label {
            font-size: 5pt;
            color: #6B7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1px;
        }
        .meta-value {
            font-size: 10pt;
            font-weight: bold;
            color: #374151;
        }

        /* Area QR Code */
        .qr-area {
            position: absolute;
            bottom: 10mm; /* Naik sedikit dari 6mm agar tidak nempel */
            left: 0;
            width: 100%;
            text-align: center;
        }
        .qr-img {
            width: 28mm;
            height: 28mm;
            border: 2px solid white; /* Border putih agar kontras */
            border-radius: 4px;
        }
        
        .card-footer {
            position: absolute;
            bottom: 2mm;
            width: 100%;
            text-align: center;
            font-size: 5pt;
            color: #9CA3AF;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>
    <div class="page-container">
        @foreach($students as $siswa)
            <div class="card-container">
                <!-- Elemen Dekorasi Background -->
                <div class="bg-deco-top"></div>
                
                <div class="card-content">
                    <!-- 1. Logo Sekolah -->
                    <div class="logo-area">
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
                    </div>

                    <!-- 2. Nama Sekolah -->
                    <div class="school-info">
                        <div class="school-name">{{ Str::limit($siswa->sekolah->nama_sekolah, 25) }}</div>
                        <div class="school-npsn">NPSN: {{ $siswa->sekolah->npsn ?? '-' }}</div>
                    </div>

                    <!-- 3. Info Siswa (Tanpa Kelas) -->
                    <div class="student-area">
                        <div class="student-name">{{ $siswa->nama_lengkap }}</div>
                        <div class="divider"></div>
                        
                        <div class="meta-label">NIS / NISN</div>
                        <div class="meta-value">{{ $siswa->nis ?? '-' }} / {{ $siswa->nisn }}</div>
                    </div>

                    <!-- 4. QR Code -->
                    <div class="qr-area">
                        <img src="data:image/png;base64, {{ base64_encode(SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(300)->margin(0)->generate($siswa->qr_code_data)) }}" class="qr-img">
                    </div>
                    
                    <div class="card-footer">KARTU PRESENSI</div>
                </div>
            </div>
        @endforeach
    </div>
</body>
</html>