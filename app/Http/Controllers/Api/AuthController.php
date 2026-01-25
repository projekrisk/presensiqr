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

        // Load user beserta data sekolah
        $user = User::with('sekolah')->where('email', $request->email)->first();

        // 1. Cek Kredensial
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Login Gagal'], 401);
        }

        // 2. Cek Masa Aktif Sekolah (Fitur Langganan)
        if ($user->sekolah && !$user->sekolah->isSubscriptionActive()) {
            return response()->json([
                'success' => false, 
                'message' => 'Masa aktif sekolah telah berakhir. Hubungi Admin Sekolah.'
            ], 403);
        }

        // 3. Buat Token
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login Berhasil',
            'token' => $token,
            'role' => 'guru',
            'user' => $user,
            'logo' => $user->sekolah ? $user->sekolah->logo : null,
            'school_name' => $user->sekolah ? $user->sekolah->nama_sekolah : 'Sekolah Tidak Diketahui',
            'user_photo' => $user->foto, 
        ]);
    }

    public function loginKiosk(Request $request)
    {
        $request->validate(['device_id' => 'required']);

        $hashedId = hash('sha256', $request->device_id);

        $perangkat = Perangkat::with('sekolah')->where('device_id_hash', $hashedId)->first();

        // 1. Cek Perangkat Terdaftar & Aktif
        if (! $perangkat || ! $perangkat->status_aktif) {
            return response()->json(['success' => false, 'message' => 'Perangkat Tidak Terdaftar / Belum Aktif'], 403);
        }

        // 2. Cek Masa Aktif Sekolah
        if ($perangkat->sekolah && !$perangkat->sekolah->isSubscriptionActive()) {
             return response()->json([
                'success' => false, 
                'message' => 'Masa aktif sekolah telah berakhir. Hubungi Admin.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Perangkat Terverifikasi',
            'token' => 'KIOSK_TOKEN',
            'user' => [
                'name' => $perangkat->nama_device,
                'email' => 'kiosk@device',
                'sekolah_id' => $perangkat->sekolah_id
            ],
            'logo' => $perangkat->sekolah ? $perangkat->sekolah->logo : null,
            'school_name' => $perangkat->sekolah ? $perangkat->sekolah->nama_sekolah : 'Sekolah',
            'user_photo' => null,
        ]);
    }
}