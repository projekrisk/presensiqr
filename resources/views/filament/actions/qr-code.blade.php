<div class="flex flex-col items-center justify-center space-y-4 p-4 text-center">
    <div class="border-4 border-black p-2 bg-white inline-block">
        @php
            $qrData = $record->qr_code_data;
            
            // 1. Logika Pencarian Logo yang Lebih Kuat
            $logoPath = null;
            if ($record->sekolah && $record->sekolah->logo) {
                $filename = $record->sekolah->logo;

                // Cek di disk 'uploads' (Prioritas utama sesuai konfigurasi kita)
                try {
                    if (\Illuminate\Support\Facades\Storage::disk('uploads')->exists($filename)) {
                        $logoPath = \Illuminate\Support\Facades\Storage::disk('uploads')->path($filename);
                    }
                    // Cek di disk 'public' (Cadangan)
                    elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists($filename)) {
                        $logoPath = \Illuminate\Support\Facades\Storage::disk('public')->path($filename);
                    }
                } catch (\Exception $e) {
                    // Abaikan error jika disk tidak ditemukan, lanjut ke cek manual
                }

                // Cek Manual (Hardcheck) jika Storage Facade gagal
                if (!$logoPath) {
                    if (file_exists(public_path('uploads/' . $filename))) {
                        $logoPath = public_path('uploads/' . $filename);
                    } elseif (file_exists(storage_path('app/public/' . $filename))) {
                        $logoPath = storage_path('app/public/' . $filename);
                    }
                }
            }

            // 2. Generate QR Code
            if ($logoPath && file_exists($logoPath)) {
                // JIKA ADA LOGO: Gunakan format PNG dan Merge
                try {
                    $pngData = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                                ->size(300)
                                ->margin(1)
                                ->errorCorrection('H') // High error correction
                                ->merge($logoPath, 0.3, true) // 0.3 = 30% ukuran, true = absolute path
                                ->generate($qrData);
                                
                    $src = 'data:image/png;base64,' . base64_encode($pngData);
                } catch (\Exception $e) {
                    // Jika merge gagal (misal format gambar tidak support), fallback ke QR biasa
                    $svgData = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                                ->size(300)
                                ->margin(1)
                                ->generate($qrData);
                    $src = 'data:image/svg+xml;base64,' . base64_encode($svgData);
                }
            } else {
                // JIKA TIDAK ADA LOGO: Fallback ke SVG
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