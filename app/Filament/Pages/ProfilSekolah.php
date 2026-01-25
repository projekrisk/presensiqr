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
use Filament\Forms\Components\Placeholder; // Import Placeholder
use Filament\Forms\Components\Hidden;      // Import Hidden
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

// Import Tambahan untuk Action
use Filament\Actions\Action;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Components\Select;
use App\Models\Paket;
use App\Models\Rekening;
use App\Models\Tagihan;

class ProfilSekolah extends Page implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions; 

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
                        
                        Tabs\Tab::make('Paket Langganan')
                            ->icon('heroicon-m-credit-card')
                            ->schema([
                                ViewField::make('info_paket')
                                    ->view('filament.forms.components.info-paket')
                                    ->viewData([
                                        'getRecord' => fn () => Auth::user()->sekolah
                                    ]),
                            ]),
                        
                        // Tab 3: Pengaturan Presensi (Sama seperti sebelumnya)
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
                // Update kolom lain jika ada di form (hari_kerja dll)
            ]);
            
            Notification::make()->success()->title('Profil Diperbarui')->send();
        }
    }

    // --- ACTION: UPGRADE PAKET (LANGSUNG TAHUNAN) ---
    public function upgradePaketAction(): Action
    {
        // Ambil paket berbayar (Asumsi hanya ada 1 paket premium, atau ambil yang pertama)
        $paketPremium = Paket::where('harga', '>', 0)->first();

        return Action::make('upgradePaket')
            ->label('Upgrade Paket')
            ->modalHeading('Konfirmasi Upgrade')
            ->modalDescription('Silakan pilih metode pembayaran untuk melanjutkan.')
            ->modalSubmitActionLabel('Buat Invoice')
            ->modalWidth('md')
            ->form([
                // Tampilkan Info Paket secara Statis (Bukan Dropdown)
                Placeholder::make('info_paket')
                    ->label('Paket Pilihan')
                    ->content(fn () => "{$paketPremium->nama_paket} - Rp " . number_format($paketPremium->harga, 0, ',', '.')),

                // Kirim ID Paket secara tersembunyi
                Hidden::make('paket_id')
                    ->default($paketPremium->id),
                    
                Select::make('rekening_id')
                    ->label('Metode Pembayaran (Transfer Bank)')
                    ->options(Rekening::where('is_active', true)->get()->mapWithKeys(function ($item) {
                        return [$item->id => "{$item->nama_bank} - {$item->nomor_rekening}"];
                    }))
                    ->required()
                    ->native(false),
            ])
            ->action(function (array $data) use ($paketPremium) {
                $sekolah = Auth::user()->sekolah;
                
                Tagihan::create([
                    'sekolah_id' => $sekolah->id,
                    'paket_id' => $data['paket_id'], // Dari hidden input
                    'rekening_id' => $data['rekening_id'],
                    'jumlah_bayar' => $paketPremium->harga,
                    'status' => 'pending',
                ]);
                
                Notification::make()->success()->title('Invoice Berhasil Dibuat')->send();
                $this->redirect(\App\Filament\Resources\TagihanResource::getUrl('index'));
            });
    }
}