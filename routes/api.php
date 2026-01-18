<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SyncController;

// 1. Route Publik (Login)
Route::post('/login/guru', [AuthController::class, 'loginGuru']);
Route::post('/login/kiosk', [AuthController::class, 'loginKiosk']);

// 2. Route Perlu Izin (Harus punya Token atau Device Hash)
// Kita buat group agar rapi
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Sync untuk Guru (Perlu Login)
    Route::get('/guru/siswa', [SyncController::class, 'getSiswa']);
});

// 3. Route Khusus Kiosk (Tanpa Token User, tapi Header Hash)
Route::post('/kiosk/sync-up', [SyncController::class, 'uploadAbsensi']);
Route::get('/kiosk/siswa', [SyncController::class, 'getSiswa']);
