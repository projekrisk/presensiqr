<?php

    use Illuminate\Support\Facades\Schedule;
    use Illuminate\Foundation\Inspiring;
    use Illuminate\Support\Facades\Artisan;

    // Contoh command bawaan (bisa dihapus atau dibiarkan)
    Artisan::command('inspire', function () {
        $this->comment(Inspiring::quote());
    })->purpose('Display an inspiring quote')->hourly();

    // --- SCHEDULER PEMBATALAN TAGIHAN ---
    // Jalankan pengecekan setiap jam
    Schedule::command('tagihan:autocancel')->hourly();