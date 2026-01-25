<x-filament-panels::page>
    <!-- BAGIAN 1: STATUS LANGGANAN -->
    @php
        $user = auth()->user();
        // Ambil data sekolah terbaru dari database (mengatasi masalah cache session)
        $sekolah = $user->sekolah->refresh();
        
        $paket = $sekolah->paket_langganan ?? 'free';
        $isPro = $paket !== 'free';
        
        // Styling Kondisional (Support Dark Mode)
        $bgClass = $isPro 
            ? 'bg-success-50 dark:bg-success-900/10 border-success-200 dark:border-success-800' 
            : 'bg-gray-50 dark:bg-gray-800/50 border-gray-200 dark:border-gray-700';
            
        $textClass = $isPro 
            ? 'text-success-700 dark:text-success-400' 
            : 'text-gray-700 dark:text-gray-400';
            
        $icon = $isPro ? 'heroicon-m-star' : 'heroicon-m-sparkles';
    @endphp

    <div class="rounded-xl border {{ $bgClass }} p-6 shadow-sm">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-filament::icon icon="{{ $icon }}" class="w-6 h-6 {{ $textClass }}"/>
                    Status Keanggotaan
                </h2>
                
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <!-- Badge Paket -->
                    <span class="px-3 py-1 rounded-full text-sm font-bold {{ $isPro ? 'bg-success-600 text-white' : 'bg-gray-600 text-white' }}">
                        {{ ucfirst($paket) }} Plan
                    </span>
                    
                    <!-- Info Expired -->
                    <span class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-1">
                        @if($sekolah->tgl_berakhir_langganan)
                            <span>Berakhir pada:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">
                                {{ \Carbon\Carbon::parse($sekolah->tgl_berakhir_langganan)->translatedFormat('d F Y') }}
                            </span>
                            <span class="text-xs text-gray-400">
                                ({{ \Carbon\Carbon::parse($sekolah->tgl_berakhir_langganan)->diffForHumans() }})
                            </span>
                        @else
                            (Versi Percobaan / Unlimited)
                        @endif
                    </span>
                </div>
            </div>

            <!-- Tombol Action Upgrade (Rendered by Livewire) -->
            <div>
                {{ $this->upgradeAction }}
            </div>
        </div>
    </div>

    <!-- BAGIAN 2: RIWAYAT PEMBAYARAN -->
    <div class="mt-8">
        <div class="mb-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Riwayat Tagihan & Pembayaran</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Daftar invoice dan status pembayaran langganan sekolah Anda.
            </p>
        </div>
        
        <!-- Render Tabel Filament di sini -->
        {{ $this->table }}
    </div>
</x-filament-panels::page>