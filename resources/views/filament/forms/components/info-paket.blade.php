<div class="grid md:grid-cols-2 gap-6 p-4">
    <!-- Status Saat Ini -->
    <div class="bg-gray-50 border border-gray-200 rounded-xl p-6">
        <h3 class="text-lg font-bold text-gray-500 mb-2">Paket Saat Ini</h3>
        
        <!-- Logika Warna Badge -->
        @php
            $paket = $getRecord()->paket_langganan ?? 'free';
            $color = match($paket) {
                'pro' => 'text-green-600 bg-green-100',
                'basic' => 'text-blue-600 bg-blue-100',
                default => 'text-gray-600 bg-gray-200',
            };
            $label = ucfirst($paket);
        @endphp

        <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold mb-4 {{ $color }}">
            {{ $label }} Plan
        </div>

        <p class="text-sm text-gray-500">
            Berlaku sampai: <br>
            <span class="text-gray-900 font-medium text-lg">
                {{ $getRecord()->tgl_berakhir_langganan ? \Carbon\Carbon::parse($getRecord()->tgl_berakhir_langganan)->translatedFormat('d F Y') : 'Selamanya (Trial)' }}
            </span>
        </p>
    </div>

    <!-- Opsi Upgrade -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 flex flex-col justify-center items-center text-center">
        <h3 class="text-lg font-bold text-blue-900 mb-2">Upgrade Layanan?</h3>
        <p class="text-sm text-blue-700 mb-4">Dapatkan fitur prioritas support dan backup data harian dengan paket tahunan.</p>
        
        <a href="[https://wa.me/6281234567890?text=Halo%20Admin,%20saya%20dari%20sekolah%20](https://wa.me/6281234567890?text=Halo%20Admin,%20saya%20dari%20sekolah%20){{ $getRecord()->nama_sekolah }}%20ingin%20upgrade%20paket." 
           target="_blank"
           class="px-4 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 transition shadow-md w-full">
            Hubungi Sales via WhatsApp
        </a>
    </div>
</div>
