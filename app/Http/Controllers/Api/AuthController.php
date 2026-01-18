<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Perangkat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // 1. LOGIN GURU (Email & Password)
    public function loginGuru(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Login Gagal'], 401);
        }

        // Cek apakah user ini Guru?
        // Hapus Token lama (opsional, agar single device)
        $user->tokens()->delete();

        // Buat Token Baru
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login Berhasil',
            'token' => $token,
            'role' => 'guru',
            'user' => $user
        ]);
    }

    // 2. LOGIN KIOSK (Device ID Binding)
    public function loginKiosk(Request $request)
    {
        $request->validate([
            'device_id' => 'required', // ID Asli dari Android
        ]);

        // Hash ID yang dikirim Android dengan SHA256 (sama seperti di DB)
        // Bisa tambah SALT jika mau lebih aman: hash('sha256', $request->device_id . 'RAHASIA');
        $hashedId = hash('sha256', $request->device_id);

        // Cari di database
        $perangkat = Perangkat::where('device_id_hash', $hashedId)->first();

        if (! $perangkat || ! $perangkat->status_aktif) {
            return response()->json(['message' => 'Perangkat Tidak Terdaftar / Belum Aktif'], 403);
        }

        // Karena Perangkat tidak punya tabel User sendiri, kita bisa return data sekolahnya
        return response()->json([
            'message' => 'Perangkat Terverifikasi',
            'sekolah_id' => $perangkat->sekolah_id,
            'nama_device' => $perangkat->nama_device,
            // Kita tidak pakai Token Sanctum untuk Kiosk di tahap ini agar simpel,
            // Kiosk cukup kirim device_id_hash di setiap request sync.
        ]);
    }
}
