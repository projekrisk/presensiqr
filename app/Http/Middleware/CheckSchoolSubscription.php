<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Illuminate\Support\Str; // Import Str

class CheckSchoolSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // 1. Cek apakah user adalah User Sekolah (Admin Sekolah ATAU Guru)
        if ($user && $user->sekolah_id && in_array($user->peran, ['admin_sekolah', 'guru'])) {
            
            // Ambil data terbaru dari DB
            $sekolah = $user->sekolah->refresh();

            // 2. Cek apakah langganan aktif
            if (!$sekolah->isSubscriptionActive()) {
                
                $routeName = $request->route()->getName();
                
                // Cek apakah sedang mengakses halaman logout (agar tidak terjebak)
                // Cek apakah sedang mengakses halaman Member Area (tempat bayar)
                $isLogout = Str::contains($routeName, 'logout');
                $isMemberArea = Str::contains($routeName, 'member-area');

                // Jika GURU: Langsung blokir total (kecuali logout)
                if ($user->peran === 'guru') {
                     if ($isLogout) return $next($request);
                     
                     // Kirim respon JSON jika request dari API (Android)
                     if ($request->expectsJson()) {
                         return response()->json(['message' => 'Masa aktif sekolah berakhir.'], 403);
                     }
                     
                     abort(403, 'Masa aktif sekolah berakhir. Hubungi Admin Sekolah.');
                }

                // Jika ADMIN SEKOLAH: Redirect ke Member Area jika mencoba akses menu lain
                if (!$isMemberArea && !$isLogout) {
                    
                    Notification::make()
                        ->warning()
                        ->title('Masa Aktif Berakhir')
                        ->body('Akses dibatasi. Silakan perpanjang paket langganan Anda di menu ini.')
                        ->persistent()
                        ->send();

                    return redirect()->route('filament.admin.pages.member-area');
                }
            }
        }

        return $next($request);
    }
}