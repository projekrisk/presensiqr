<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\Api\GuruAbsensiController;
use App\Http\Controllers\Api\LaporanGuruController; // <-- Import Controller Baru

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// 1. Route Publik
Route::post('/login/guru', [AuthController::class, 'loginGuru']);
Route::post('/login/kiosk', [AuthController::class, 'loginKiosk']);

// 2. Route Perlu Login (Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Sync
    Route::get('/guru/siswa', [SyncController::class, 'getSiswa']);

    // Absensi Harian (Cek & Simpan Jurnal)
    Route::get('/guru/absensi-check', [GuruAbsensiController::class, 'check']);
    Route::post('/guru/jurnal', [GuruAbsensiController::class, 'store']);

    // --- ROUTE LAPORAN (BARU) ---
    Route::get('/guru/laporan/summary', [LaporanGuruController::class, 'summary']);
    Route::get('/guru/laporan/detail', [LaporanGuruController::class, 'detail']);
});

// 3. Route Kiosk (Device Hash)
Route::post('/kiosk/sync-up', [SyncController::class, 'uploadAbsensi']);
Route::get('/kiosk/siswa', [SyncController::class, 'getSiswa']);