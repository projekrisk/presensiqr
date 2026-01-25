@if($this->shouldRender())
    @php
        $sekolah = Illuminate\Support\Facades\Auth::user()->sekolah;
        $paket = $sekolah->paket_langganan ?? 'free';
        $isFree = $paket === 'free';
        
        // Warna background & border (Light & Dark Mode)
        // Free: Abu-abu di light, Abu gelap di dark
        // Pro: Hijau muda di light, Hijau tua transparan di dark
        $containerClass = $isFree 
            ? 'bg-gray-50 border-gray-200 dark:bg-gray-800 dark:border-gray-700' 
            : 'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800';
            
        $iconBgClass = $isFree
            ? 'bg-white dark:bg-gray-700'
            : 'bg-white dark:bg-green-900';
            
        $textClass = $isFree 
            ? 'text-gray-600 dark:text-gray-400' 
            : 'text-green-700 dark:text-green-400';
            
        $icon = $isFree ? 'heroicon-m-sparkles' : 'heroicon-m-star';
    @endphp

    <div class="px-4 pb-4 mt-auto">
        <div class="p-4 rounded-xl border {{ $containerClass }} shadow-sm">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-1.5 rounded-lg shadow-sm {{ $iconBgClass }}">
                    @svg($icon, 'w-5 h-5 ' . $textClass)
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider opacity-70 {{ $textClass }}">Paket Anda</p>
                    <p class="text-sm font-bold {{ $textClass }}">
                        {{ ucfirst($paket) }} Plan
                    </p>
                </div>
            </div>

            <div class="text-xs {{ $textClass }} opacity-80 mb-4 ml-1">
                @if($sekolah->tgl_berakhir_langganan)
                    Exp: {{ \Carbon\Carbon::parse($sekolah->tgl_berakhir_langganan)->format('d M Y') }}
                @else
                    Mode Percobaan
                @endif
            </div>

            <!-- Tombol Action Filament (Akan memicu Modal) -->
            <div class="w-full">
                {{ ($this->upgradeAction)(['class' => 'w-full justify-center']) }}
            </div>
            
            <!-- Area ini wajib ada untuk merender modal Filament -->
            <x-filament-actions::modals />
        </div>
    </div>
@endif