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

        // 1. Cek apakah user adalah Admin Sekolah
        if ($user && $user->sekolah_id && $user->peran === 'admin_sekolah') {
            
            // 2. Cek apakah langganan aktif
            if (!$user->sekolah->isSubscriptionActive()) {
                
                // 3. Cek apakah dia sedang membuka halaman Member Area?
                // Kita izinkan akses ke Member Area agar dia bisa bayar/upgrade
                // Ganti 'member-area' sesuai slug page Anda
                if (!$request->routeIs('filament.admin.pages.member-area') && 
                    !$request->routeIs('filament.admin.auth.logout')) { // Izinkan logout juga
                    
                    Notification::make()
                        ->warning()
                        ->title('Masa Aktif Berakhir')
                        ->body('Silakan perpanjang paket langganan Anda untuk mengakses menu ini.')
                        ->persistent()
                        ->send();

                    // Redirect paksa ke Member Area
                    return redirect()->route('filament.admin.pages.member-area');
                }
            }
        }

        return $next($request);
    }
}