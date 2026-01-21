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
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ProfilSekolah extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationLabel = 'Profil Sekolah';
    protected static ?string $title = 'Pengaturan Sekolah';
    protected static string $view = 'filament.pages.profil-sekolah';
    protected static ?int $navigationSort = 1; // Paling atas

    public ?array $data = [];

    // Filter: Hanya tampilkan menu ini untuk Admin Sekolah (yang punya sekolah_id)
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check() && Auth::user()->sekolah_id !== null;
    }

    public function mount(): void
    {
        $sekolah = Auth::user()->sekolah;

        if ($sekolah) {
            $this->form->fill([
                'nama_sekolah' => $sekolah->nama_sekolah,
                'npsn' => $sekolah->npsn,
                'alamat' => $sekolah->alamat,
                'email_admin' => $sekolah->email_admin,
                'logo' => $sekolah->logo,
                // Data paket hanya untuk display, tidak disimpan dari form ini
            ]);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        // TAB 1: Identitas
                        Tabs\Tab::make('Identitas Sekolah')
                            ->icon('heroicon-m-information-circle')
                            ->schema([
                                FileUpload::make('logo')
                                    ->label('Logo Sekolah (Akan tampil di Aplikasi)')
                                    ->disk('uploads')
                                    ->directory('sekolah-logo')
                                    ->image()
                                    ->imageEditor()
                                    ->avatar()
                                    ->alignCenter()
                                    ->columnSpanFull(),
                                
                                TextInput::make('nama_sekolah')
                                    ->required()
                                    ->label('Nama Resmi Sekolah'),
                                
                                TextInput::make('npsn')
                                    ->disabled()
                                    ->dehydrated(false) // Jangan kirim saat save
                                    ->label('NPSN (Hubungi Admin Pusat untuk ubah)'),
                                
                                TextInput::make('alamat')
                                    ->columnSpanFull(),
                                
                                TextInput::make('email_admin')
                                    ->email()
                                    ->label('Email Kontak'),
                            ])->columns(2),
                        
                        // TAB 2: Langganan
                        Tabs\Tab::make('Paket Langganan')
                            ->icon('heroicon-m-credit-card')
                            ->schema([
                                // Panggil Custom View yang kita buat di langkah 3
                                ViewField::make('info_paket')
                                    ->view('filament.forms.components.info-paket')
                                    ->viewData([
                                        'getRecord' => fn () => Auth::user()->sekolah
                                    ]),
                            ]),
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
            ]);

            Notification::make()
                ->success()
                ->title('Profil Sekolah Berhasil Diperbarui')
                ->send();
        }
    }
}
