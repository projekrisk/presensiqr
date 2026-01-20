<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Perangkat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function loginGuru(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Load user beserta data sekolahnya untuk ambil logo
        $user = User::with('sekolah')->where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Login Gagal'], 401);
        }

        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login Berhasil',
            'token' => $token,
            'role' => 'guru',
            'user' => $user,
            'logo' => $user->sekolah ? $user->sekolah->logo : null, // Kirim Logo
        ]);
    }

    public function loginKiosk(Request $request)
    {
        $request->validate(['device_id' => 'required']);

        $hashedId = hash('sha256', $request->device_id);

        // Load Perangkat beserta data Sekolah
        $perangkat = Perangkat::with('sekolah')->where('device_id_hash', $hashedId)->first();

        if (! $perangkat || ! $perangkat->status_aktif) {
            return response()->json(['success' => false, 'message' => 'Perangkat Tidak Terdaftar / Belum Aktif'], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Perangkat Terverifikasi',
            'token' => 'KIOSK_TOKEN', // Token dummy
            // Buat struktur user dummy agar compatible dengan model Android
            'user' => [
                'name' => $perangkat->nama_device,
                'email' => 'kiosk@device',
                'sekolah_id' => $perangkat->sekolah_id
            ],
            'logo' => $perangkat->sekolah ? $perangkat->sekolah->logo : null, // Kirim Logo
        ]);
    }
}