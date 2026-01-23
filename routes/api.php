<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\Api\GuruAbsensiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
*/

// 1. Route Publik (Login & Auth)
Route::post('/login/guru', [AuthController::class, 'loginGuru']);
Route::post('/login/kiosk', [AuthController::class, 'loginKiosk']);

// 2. Route Perlu Izin (Harus punya Token Login Guru)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Sync Data Siswa untuk Guru
    Route::get('/guru/siswa', [SyncController::class, 'getSiswa']);

    // Cek Riwayat Absensi (Fitur DatePicker - BARU)
    Route::get('/guru/absensi-check', [GuruAbsensiController::class, 'check']);
});

// 3. Route Khusus Kiosk (Tanpa Token User, Validasi via Header X-Device-Hash)
// Logic validasi ada di dalam Controller
Route::post('/kiosk/sync-up', [SyncController::class, 'uploadAbsensi']);
Route::get('/kiosk/siswa', [SyncController::class, 'getSiswa']);
