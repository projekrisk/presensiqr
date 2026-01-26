<?php
   
   use Illuminate\Support\Facades\Route;
   use App\Http\Controllers\RegisterSchoolController;
   use App\Http\Controllers\DownloadTemplateController;
   use App\Http\Controllers\CetakKartuController;
   use App\Http\Controllers\DownloadQrController; 

   // Halaman Depan (Landing Page)
   Route::get('/', function () {
       return view('welcome');
   });
   
   // Route Pendaftaran Sekolah
   Route::post('/register-school', [RegisterSchoolController::class, 'store'])->name('register.school');
   
   // Route Download Template Excel (Harus Login)
   Route::get('/download-template-siswa', [DownloadTemplateController::class, 'downloadTemplateSiswa'])
       ->middleware('auth') // Wajib login untuk download
       ->name('download.template.siswa');
   
   // Route Migrasi Darurat (Opsional, hapus jika sudah production)
   Route::get('/migrate-force', function() {
       \Illuminate\Support\Facades\Artisan::call('migrate --force');
       return 'Migrasi Selesai';
   });

   Route::middleware('auth')->group(function () {
        // 1. Download Kartu Pelajar (ZIP)
        Route::get('/cetak-kartu', [CetakKartuController::class, 'cetak'])->name('cetak.kartu');
        
        // 2. Download QR Only (ZIP)
        Route::get('/download-qr', [DownloadQrController::class, 'download'])->name('download.qr');
    });
