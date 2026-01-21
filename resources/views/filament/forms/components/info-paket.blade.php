@php
    $sekolah = $getRecord();
    $tagihanPending = \App\Models\Tagihan::where('sekolah_id', $sekolah->id)
        ->where('status', 'pending')
        ->latest()
        ->first();
@endphp

<div class="grid md:grid-cols-2 gap-6 p-4">
    <!-- Status Saat Ini -->
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm flex flex-col justify-between">
        <div>
            <h3 class="text-lg font-bold text-gray-500 dark:text-gray-400 mb-4">Status Langganan</h3>
            
            @php
                $paket = $sekolah->paket_langganan ?? 'free';
                // Konfigurasi tampilan berdasarkan paket
                $config = match($paket) {
                    'pro' => [
                        'icon' => 'heroicon-m-star',
                        'bg' => 'bg-success-50 dark:bg-success-900/20',
                        'border' => 'border-success-200 dark:border-success-800',
                        'text' => 'text-success-700 dark:text-success-400',
                    ],
                    'basic' => [
                        'icon' => 'heroicon-m-sparkles',
                        'bg' => 'bg-info-50 dark:bg-info-900/20',
                        'border' => 'border-info-200 dark:border-info-800',
                        'text' => 'text-info-700 dark:text-info-400',
                    ],
                    default => [
                        'icon' => 'heroicon-m-check-circle',
                        'bg' => 'bg-gray-50 dark:bg-gray-800',
                        'border' => 'border-gray-200 dark:border-gray-700',
                        'text' => 'text-gray-700 dark:text-gray-400',
                    ],
                };
                $label = ucfirst($paket);
            @endphp

            <!-- Card Paket Aktif -->
            <div class="flex items-center gap-4 p-4 rounded-lg border {{ $config['bg'] }} {{ $config['border'] }} mb-4">
                <div class="p-2 rounded-full bg-white dark:bg-gray-900 shadow-sm">
                    <x-filament::icon
                        :icon="$config['icon']"
                        class="h-6 w-6 {{ $config['text'] }}"
                    />
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider {{ $config['text'] }} opacity-80">Paket Anda</p>
                    <h2 class="text-2xl font-black {{ $config['text'] }}">{{ $label }} Plan</h2>
                </div>
            </div>
        </div>

        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Berlaku sampai: <br>
                <span class="text-gray-900 dark:text-white font-medium text-lg">
                    {{ $sekolah->tgl_berakhir_langganan ? \Carbon\Carbon::parse($sekolah->tgl_berakhir_langganan)->translatedFormat('d F Y') : 'Selamanya (Trial)' }}
                </span>
            </p>
        </div>
    </div>

    <!-- Opsi Upgrade / Status Tagihan -->
    <div class="bg-primary-50 dark:bg-primary-900/10 border border-primary-200 dark:border-primary-800 rounded-xl p-6 flex flex-col justify-center items-center text-center">
        @if($tagihanPending)
            <!-- Jika ada tagihan pending -->
            <div class="p-3 bg-warning-100 dark:bg-warning-900/30 rounded-full mb-3">
                <x-filament::icon icon="heroicon-o-clock" class="w-8 h-8 text-warning-600 dark:text-warning-400" />
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Menunggu Pembayaran</h3>
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                Invoice <strong>{{ $tagihanPending->nomor_invoice }}</strong> belum dibayar.
            </p>
            <x-filament::button :href="\App\Filament\Resources\TagihanResource::getUrl('index')" tag="a" color="warning" class="w-full">
                Lihat Tagihan & Upload Bukti
            </x-filament::button>
        @else
            <!-- Jika tidak ada tagihan -->
            <div class="p-3 bg-primary-100 dark:bg-primary-900/30 rounded-full mb-3">
                <x-filament::icon icon="heroicon-o-rocket-launch" class="w-8 h-8 text-primary-600 dark:text-primary-400" />
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Upgrade Layanan?</h3>
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">Dapatkan fitur prioritas support dan backup data harian.</p>
            
            <x-filament::button wire:click="mountAction('upgradePaket')" class="w-full">
                Pilih Paket Langganan
            </x-filament::button>
        @endif
    </div>
</div>
