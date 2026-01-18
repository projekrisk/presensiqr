<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jalankan perintah mark-alpha setiap hari jam 10:00 WIB
Schedule::command('absensi:mark-alpha')
        ->dailyAt('10:00')
        ->timezone('Asia/Jakarta');