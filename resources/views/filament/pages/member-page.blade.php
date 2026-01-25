<x-filament-panels::page>
    <!-- BAGIAN 1: STATUS LANGGANAN -->
    @php
        $sekolah = auth()->user()->sekolah;
        $paket = $sekolah->paket_langganan ?? 'free';
        $isPro = $paket !== 'free';
        $bgClass = $isPro ? 'bg-success-50 dark:bg-success-900/20 border-success-200' : 'bg-gray-50 dark:bg-gray-800 border-gray-200';
        $textClass = $isPro ? 'text-success-700 dark:text-success-400' : 'text-gray-700 dark:text-gray-400';
    @endphp

    <div class="rounded-xl border {{ $bgClass }} p-6 shadow-sm">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-filament::icon icon="heroicon-m-identification" class="w-6 h-6 {{ $textClass }}"/>
                    Status Keanggotaan
                </h2>
                
                <div class="mt-2">
                    <span class="px-3 py-1 rounded-full text-sm font-bold {{ $isPro ? 'bg-success-600 text-white' : 'bg-gray-600 text-white' }}">
                        {{ ucfirst($paket) }} Plan
                    </span>
                    <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">
                        @if($sekolah->tgl_berakhir_langganan)
                            Berakhir pada: {{ \Carbon\Carbon::parse($sekolah->tgl_berakhir_langganan)->translatedFormat('d F Y') }}
                        @else
                            (Versi Percobaan)
                        @endif
                    </span>
                </div>
            </div>

            <!-- Tombol Action Upgrade -->
            <div>
                {{ $this->upgradeAction }}
            </div>
        </div>
    </div>

    <!-- BAGIAN 2: RIWAYAT PEMBAYARAN -->
    <div class="mt-8">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Riwayat Tagihan & Pembayaran</h3>
        <!-- Render Tabel Filament di sini -->
        {{ $this->table }}
    </div>
</x-filament-panels::page>