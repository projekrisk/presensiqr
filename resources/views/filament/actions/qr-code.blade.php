<div class="flex flex-col items-center justify-center space-y-4 p-4 text-center">
    <div class="border-4 border-black p-2 bg-white inline-block">
        @php
            $qrData = $record->qr_code_data;
            
            // 1. Ambil Path Logo Sekolah
            $logoPath = null;
            if ($record->sekolah && $record->sekolah->logo) {
                 // Pastikan file benar-benar ada di storage
                 $path = storage_path('app/public/' . $record->sekolah->logo);
                 if (file_exists($path)) {
                     $logoPath = $path;
                 }
            }

            // 2. Generate QR Code
            if ($logoPath) {
                // JIKA ADA LOGO: Gunakan format PNG dan Merge
                // errorCorrection('H') (High) sangat penting agar QR tetap bisa discan meski tertutup logo
                $pngData = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                            ->size(300)
                            ->margin(1)
                            ->errorCorrection('H') 
                            ->merge($logoPath, 0.3, true) // 0.3 = Logo menempati 30% area QR
                            ->generate($qrData);
                            
                // Encode ke Base64 agar bisa tampil di img tag
                $src = 'data:image/png;base64,' . base64_encode($pngData);
            } else {
                // JIKA TIDAK ADA LOGO: Fallback ke SVG (Lebih tajam)
                $svgData = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                            ->size(300)
                            ->margin(1)
                            ->generate($qrData);
                            
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