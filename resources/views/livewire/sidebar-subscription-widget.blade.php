@if($this->shouldRender())
    @php
        $sekolah = Illuminate\Support\Facades\Auth::user()->sekolah;
        $paket = $sekolah->paket_langganan ?? 'free';
        $isFree = $paket === 'free';
        
        // Warna background berdasarkan paket
        $bgClass = $isFree ? 'bg-gray-100 border-gray-200' : 'bg-green-50 border-green-200';
        $textClass = $isFree ? 'text-gray-600' : 'text-green-700';
        $icon = $isFree ? 'heroicon-m-sparkles' : 'heroicon-m-star';
    @endphp

    <div class="px-4 pb-4 mt-auto">
        <div class="p-4 rounded-xl border {{ $bgClass }} shadow-sm">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-1.5 bg-white rounded-lg shadow-sm">
                    @svg($icon, 'w-5 h-5 ' . $textClass)
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Paket Anda</p>
                    <p class="text-sm font-bold {{ $textClass }}">
                        {{ ucfirst($paket) }} Plan
                    </p>
                </div>
            </div>

            <div class="text-xs text-gray-500 mb-4">
                @if($sekolah->tgl_berakhir_langganan)
                    Exp: {{ \Carbon\Carbon::parse($sekolah->tgl_berakhir_langganan)->format('d M Y') }}
                @else
                    Mode Percobaan
                @endif
            </div>

            <!-- Tombol Action Filament -->
            <div class="w-full">
                {{ ($this->upgradeAction)(['class' => 'w-full justify-center']) }}
            </div>
        </div>
    </div>
@endif