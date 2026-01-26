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
            height: 13mm;
            background: #2563EB; /* Biru Utama */
            width: 100%;
            position: absolute;
            top: 0;
            left: 0;
        }

        /* Logo Tanpa Background Bulat */
        .school-logo {
            position: absolute;
            top: 1.5mm;
            left: 3mm;
            width: 10mm;
            height: 10mm;
            z-index: 10;
            object-fit: contain; /* Agar logo utuh */
        }

        /* Container Nama Sekolah & NPSN */
        .header-text {
            position: absolute;
            top: 2mm;
            left: 15mm; /* Jarak dari logo */
            right: 2mm;
        }

        .school-name {
            color: white;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .school-npsn {
            color: #bfdbfe; /* Biru muda */
            font-size: 6pt;
            margin-top: 1px;
        }

        /* Konten Utama */
        .content-area {
            position: absolute;
            top: 14mm; /* Di bawah header */
            left: 0;
            width: 100%;
            padding: 0 4mm;
            box-sizing: border-box;
        }

        /* Baris 1: Nama Siswa (Full Width) */
        .student-name-row {
            width: 100%;
            margin-bottom: 2mm;
            border-bottom: 1px solid #f3f4f6;
            padding-bottom: 1mm;
        }

        .student-name {
            font-size: 11pt;
            font-weight: 800;
            color: #111827;
            text-transform: uppercase;
            white-space: nowrap; /* Mencegah turun baris */
            overflow: hidden; /* Potong jika kepanjangan */
            text-overflow: ellipsis;
        }

        /* Baris 2: Kolom Info & QR */
        .details-row {
            width: 100%;
            height: 28mm;
        }

        /* Kolom Kiri: Detail */
        .info-col {
            float: left;
            width: 55%;
        }

        .label {
            font-size: 6pt;
            color: #6B7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0px;
        }

        .value {
            font-size: 9pt;
            font-weight: bold;
            color: #374151;
            margin-bottom: 3mm;
        }

        /* Kolom Kanan: QR Code */
        .qr-col {
            float: right;
            width: 40%;
            text-align: right;
        }

        .qr-img {
            width: 26mm; 
            height: 26mm;
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
                
                <!-- Nama & NPSN -->
                <div class="header-text">
                    <div class="school-name">{{ Str::limit($siswa->sekolah->nama_sekolah, 35) }}</div>
                    <div class="school-npsn">NPSN: {{ $siswa->sekolah->npsn ?? '-' }}</div>
                </div>

                <!-- Konten -->
                <div class="content-area">
                    
                    <!-- Baris 1: Nama Siswa Full -->
                    <div class="student-name-row">
                        <div class="student-name">{{ $siswa->nama_lengkap }}</div>
                    </div>

                    <!-- Baris 2: Split Info & QR -->
                    <div class="details-row">
                        <!-- Kiri -->
                        <div class="info-col">
                            <div class="label">NIS / NISN</div>
                            <div class="value">{{ $siswa->nis ?? '-' }} / {{ $siswa->nisn }}</div>

                            <div class="label">Kelas</div>
                            <div class="value">{{ $siswa->kelas->nama_kelas }}</div>
                        </div>

                        <!-- Kanan -->
                        <div class="qr-col">
                            <!-- Generate QR -->
                            <img src="data:image/png;base64, {{ base64_encode(SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(200)->margin(0)->generate($siswa->qr_code_data)) }}" class="qr-img">
                        </div>
                    </div>
                </div>

                <div class="footer-strip"></div>
            </div>
        @endforeach
    </div>
</body>
</html>