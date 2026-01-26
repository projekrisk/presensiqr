<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Cetak Kartu Pelajar</title>
    <style>
        @page { margin: 0; size: A4; }
        body { margin: 0; font-family: sans-serif; background: #e0e0e0; }
        
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
            border: 1px solid #ccc;
            float: left;
            margin: 0 5mm 5mm 0;
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            page-break-inside: avoid;
        }

        /* Desain Modern */
        .card-header {
            height: 12mm;
            background: linear-gradient(90deg, #2563EB, #1E40AF); /* Biru Modern */
            width: 100%;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 0;
        }

        .school-logo {
            position: absolute;
            top: 3mm;
            left: 3mm;
            width: 12mm;
            height: 12mm;
            background: white;
            border-radius: 50%;
            padding: 1px;
            z-index: 10;
            object-fit: cover;
        }

        .school-name {
            position: absolute;
            top: 3mm;
            left: 17mm;
            color: white;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            z-index: 10;
        }

        .student-photo {
            position: absolute;
            top: 16mm;
            left: 3mm;
            width: 22mm;
            height: 28mm;
            border-radius: 4px;
            object-fit: cover;
            border: 1px solid #ddd;
        }

        .student-info {
            position: absolute;
            top: 16mm;
            left: 28mm;
            width: 55mm;
        }

        .student-name {
            font-size: 10pt;
            font-weight: bold;
            color: #333;
            margin-bottom: 2px;
        }

        .student-detail {
            font-size: 8pt;
            color: #555;
            margin-bottom: 1px;
        }

        .qr-code {
            position: absolute;
            bottom: 3mm;
            right: 3mm;
            width: 18mm;
            height: 18mm;
        }

        .footer-bar {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 4mm;
            background: #f3f4f6;
            border-top: 1px solid #eee;
            font-size: 6pt;
            text-align: center;
            line-height: 4mm;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="page-container">
        @foreach($students as $siswa)
            <div class="card-container">
                <!-- Header -->
                <div class="card-header"></div>
                
                <!-- Logo Sekolah -->
                @php
                    $logoPath = public_path('images/default-logo.png'); // Default
                    if($siswa->sekolah->logo && file_exists(public_path('uploads/'.$siswa->sekolah->logo))) {
                        $logoPath = public_path('uploads/'.$siswa->sekolah->logo);
                    }
                    $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
                @endphp
                <img src="{{ $logoBase64 }}" class="school-logo">
                <div class="school-name">{{ $siswa->sekolah->nama_sekolah }}</div>

                <!-- Foto Siswa -->
                @php
                    $fotoPath = public_path('images/default-user.png'); // Perlu siapin gambar default
                    if($siswa->foto && file_exists(public_path('uploads/'.$siswa->foto))) {
                        $fotoPath = public_path('uploads/'.$siswa->foto);
                    }
                    // Cek jika file ada sebelum get contents
                    if(file_exists($fotoPath)) {
                        $fotoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($fotoPath));
                    } else {
                        $fotoBase64 = ''; // Handle empty
                    }
                @endphp
                @if($fotoBase64)
                    <img src="{{ $fotoBase64 }}" class="student-photo">
                @endif

                <!-- Info -->
                <div class="student-info">
                    <div class="student-name">{{ $siswa->nama_lengkap }}</div>
                    <div class="student-detail">NISN: {{ $siswa->nisn }}</div>
                    <div class="student-detail">Kelas: {{ $siswa->kelas->nama_kelas }}</div>
                </div>

                <!-- QR Code (Generate langsung di View) -->
                <div class="qr-code">
                    <img src="data:image/png;base64, {{ base64_encode(SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(100)->margin(0)->generate($siswa->qr_code_data)) }}" width="100%">
                </div>

                <div class="footer-bar">KARTU PRESENSI SISWA</div>
            </div>
        @endforeach
    </div>
</body>
</html>
