<div class="flex flex-col items-center justify-center space-y-4 p-4 text-center">
    <div class="border-4 border-black p-2 bg-white">
        <!-- Generate QR Code dari data terenkripsi -->
        <!-- Format SVG agar tajam saat dicetak -->
        {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(250)->generate($record->qr_code_data) !!}
    </div>

    <div>
        <h2 class="text-xl font-bold text-gray-800">{{ $record->nama_lengkap }}</h2>
        <p class="text-sm text-gray-500 font-mono">NISN: {{ $record->nisn }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $record->kelas->nama_kelas ?? '-' }}</p>
    </div>

    <div class="pt-4 print:hidden">
        <p class="text-xs text-gray-400 mb-2">Klik kanan pada QR untuk menyimpan gambar, atau gunakan tombol cetak browser (Ctrl+P).</p>
    </div>
</div>
