@php
    $sekolah = $getRecord();
    $tagihanPending = \App\Models\Tagihan::where('sekolah_id', $sekolah->id)
        ->where('status', 'pending')
        ->latest()
        ->first();
    
    $paket = $sekolah->paket_langganan ?? 'free';
    $isPro = $paket !== 'free';
@endphp

<div class="grid md:grid-cols-3 gap-6 p-4">
    <!-- KARTU STATUS (Lebar 2 Kolom) -->
    <div class="md:col-span-2 relative overflow-hidden rounded-2xl border shadow-md {{ $isPro ? 'border-primary-500' : 'border-gray-200 dark:border-gray-700' }}">
        
        <!-- Background Gradient -->
        <div class="absolute inset-0 {{ $isPro ? 'bg-gradient-to-br from-primary-600 to-primary-800' : 'bg-white dark:bg-gray-900' }} z-0"></div>
        
        <!-- Dekorasi Circle (Hanya untuk Pro) -->
        @if($isPro)
            <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -ml-10 -mb-10 w-40 h-40 rounded-full bg-black/10 blur-2xl"></div>
        @endif

        <div class="relative z-10 p-8 flex flex-col h-full justify-between">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-semibold tracking-wider uppercase mb-1 {{ $isPro ? 'text-primary-100' : 'text-gray-500 dark:text-gray-400' }}">
                        Paket Aktif Saat Ini
                    </p>
                    <h2 class="text-3xl font-black {{ $isPro ? 'text-white' : 'text-gray-900 dark:text-white' }}">
                        {{ ucfirst($paket) }} Plan
                    </h2>
                </div>
                <!-- Icon Badge -->
                <div class="p-3 rounded-xl {{ $isPro ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500 dark:bg-gray-800' }}">
                    <x-filament::icon 
                        icon="{{ $isPro ? 'heroicon-m-star' : 'heroicon-m-sparkles' }}" 
                        class="w-8 h-8" 
                    />
                </div>
            </div>

            <div class="mt-8 pt-6 border-t {{ $isPro ? 'border-white/20' : 'border-gray-100 dark:border-gray-800' }}">
                <div class="flex items-center gap-2">
                    <x-filament::icon icon="heroicon-m-calendar" class="w-5 h-5 {{ $isPro ? 'text-primary-200' : 'text-gray-400' }}"/>
                    <span class="text-sm font-medium {{ $isPro ? 'text-primary-50' : 'text-gray-600 dark:text-gray-300' }}">
                        Masa Aktif: 
                        @if($sekolah->tgl_berakhir_langganan)
                            <span class="font-bold">{{ \Carbon\Carbon::parse($sekolah->tgl_berakhir_langganan)->translatedFormat('d F Y') }}</span>
                            ({{ \Carbon\Carbon::parse($sekolah->tgl_berakhir_langganan)->diffForHumans() }})
                        @else
                            <span class="font-bold">Selamanya (Mode Trial)</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- KARTU ACTION (Lebar 1 Kolom) -->
    <div class="md:col-span-1 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-2xl p-6 flex flex-col justify-center items-center text-center">
        @if($tagihanPending)
            <!-- STATUS: MENUNGGU PEMBAYARAN -->
            <div class="w-16 h-16 bg-warning-100 dark:bg-warning-900/30 rounded-full flex items-center justify-center mb-4 animate-pulse">
                <x-filament::icon icon="heroicon-m-clock" class="w-8 h-8 text-warning-600" />
            </div>
            
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Tagihan Pending</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-6">
                Invoice #{{ $tagihanPending->nomor_invoice }} menunggu pembayaran.
            </p>
            
            <x-filament::button :href="\App\Filament\Resources\TagihanResource::getUrl('index')" tag="a" color="warning" class="w-full">
                Bayar Sekarang
            </x-filament::button>

        @elseif(!$isPro)
            <!-- STATUS: FREE (TAWARKAN UPGRADE) -->
            <div class="w-16 h-16 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center mb-4">
                <x-filament::icon icon="heroicon-m-rocket-launch" class="w-8 h-8 text-primary-600" />
            </div>
            
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Upgrade ke Pro</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-6">
                Dapatkan fitur backup otomatis & support prioritas.
            </p>
            
            <!-- Tombol Trigger Action -->
            <x-filament::button wire:click="mountAction('upgradePaket')" class="w-full shadow-lg shadow-primary-500/30">
                Upgrade Sekarang
            </x-filament::button>

        @else
            <!-- STATUS: SUDAH PRO -->
            <div class="w-16 h-16 bg-success-100 dark:bg-success-900/30 rounded-full flex items-center justify-center mb-4">
                <x-filament::icon icon="heroicon-m-shield-check" class="w-8 h-8 text-success-600" />
            </div>
            
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Akun Premium</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-6">
                Sekolah Anda menikmati layanan terbaik kami.
            </p>
            
            <x-filament::button color="gray" class="w-full" disabled>
                Perpanjang Nanti
            </x-filament::button>
        @endif
    </div>
</div>