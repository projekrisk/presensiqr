<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class CheckSchoolSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // 1. Cek apakah user adalah User Sekolah (Admin Sekolah ATAU Guru)
        // PERBAIKAN: Tambahkan 'guru' ke dalam array pengecekan
        if ($user && $user->sekolah_id && in_array($user->peran, ['admin_sekolah', 'guru'])) {
            
            // Ambil data terbaru dari DB untuk menghindari cache session lama
            $sekolah = $user->sekolah->refresh();

            // 2. Cek apakah langganan aktif
            if (!$sekolah->isSubscriptionActive()) {
                
                // Jika Guru: Langsung abort 403 (Karena guru tidak punya akses Member Area untuk bayar)
                if ($user->peran === 'guru') {
                     // Kecuali logout
                     if ($request->routeIs('filament.admin.auth.logout')) return $next($request);
                     
                     abort(403, 'Masa aktif sekolah berakhir. Hubungi Admin Sekolah.');
                }

                // Jika Admin Sekolah: Redirect ke Member Area untuk bayar
                // Izinkan akses ke halaman Member Area dan Logout
                if (!$request->routeIs('filament.admin.pages.member-area') && 
                    !$request->routeIs('filament.admin.auth.logout')) {
                    
                    Notification::make()
                        ->warning()
                        ->title('Masa Aktif Berakhir')
                        ->body('Silakan perpanjang paket langganan Anda untuk mengakses menu ini.')
                        ->persistent()
                        ->send();

                    return redirect()->route('filament.admin.pages.member-area');
                }
            }
        }

        return $next($request);
    }
}