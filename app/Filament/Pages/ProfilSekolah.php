<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Hidden;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ProfilSekolah extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationLabel = 'Profil Sekolah';
    protected static ?string $title = 'Pengaturan Sekolah';
    protected static string $view = 'filament.pages.profil-sekolah';
    protected static ?int $navigationSort = 1;

    public ?array $data = [];

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check() && Auth::user()->sekolah_id !== null && Auth::user()->peran === 'admin_sekolah';
    }

    public function mount(): void
    {
        if (Auth::user()->peran !== 'admin_sekolah') {
            abort(403, 'Akses Ditolak. Halaman ini khusus Admin Sekolah.');
        }

        $sekolah = Auth::user()->sekolah;

        if ($sekolah) {
            $this->form->fill([
                'nama_sekolah' => $sekolah->nama_sekolah,
                'npsn' => $sekolah->npsn,
                'alamat' => $sekolah->alamat,
                'email_admin' => $sekolah->email_admin,
                'logo' => $sekolah->logo,
                'hari_kerja' => $sekolah->hari_kerja ?? [],
                'jam_mulai_absen' => $sekolah->jam_mulai_absen,
                'jam_masuk' => $sekolah->jam_masuk,
                'jam_pulang' => $sekolah->jam_pulang,
            ]);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Identitas Sekolah')
                            ->icon('heroicon-m-information-circle')
                            ->schema([
                                FileUpload::make('logo')
                                    ->label('Logo Sekolah')
                                    ->disk('uploads')
                                    ->directory('sekolah-logo')
                                    ->image()
                                    ->avatar()
                                    ->columnSpanFull(),
                                TextInput::make('nama_sekolah')->required(),
                                TextInput::make('npsn')->disabled(),
                                TextInput::make('alamat')->columnSpanFull(),
                                TextInput::make('email_admin')->email(),
                            ])->columns(2),
                        
                        // TAB PAKET LANGGANAN DIHAPUS (Dipindah ke Member Area)

                        // Tab Pengaturan Presensi (Jika ada di Tahap 28) akan tetap ada di sini
                        // ...
                    ])->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $sekolah = Auth::user()->sekolah;
        
        if ($sekolah) {
            $sekolah->update([
                'nama_sekolah' => $data['nama_sekolah'],
                'alamat'       => $data['alamat'],
                'email_admin'  => $data['email_admin'],
                'logo'         => $data['logo'],
                // Update kolom lain jika ada di form
            ]);
            
            Notification::make()->success()->title('Profil Diperbarui')->send();
        }
    }
}