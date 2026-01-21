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

    // --- IZIN AKSES: HANYA ADMIN SEKOLAH (Operator DILARANG) ---
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check() 
            && Auth::user()->sekolah_id !== null 
            && Auth::user()->peran === 'admin_sekolah';
    }

    public function mount(): void
    {
        // Double check saat halaman diakses langsung via URL
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
            Notification::make()->success()->title('Profil Diperbarui')->send();
        }
    }

    public function upgradePaketAction(): Action
    {
        return Action::make('upgradePaket')
            ->label('Upgrade Paket')
            ->modalHeading('Pilih Paket Langganan')
            ->form([
                Select::make('paket_id')
                    ->label('Pilih Paket')
                    ->options(Paket::where('is_active', true)->pluck('nama_paket', 'id'))
                    ->required()
                    ->reactive(),
                    
                Select::make('rekening_id')
                    ->label('Metode Pembayaran (Transfer Bank)')
                    ->options(Rekening::where('is_active', true)->get()->mapWithKeys(function ($item) {
                        return [$item->id => "{$item->nama_bank} - {$item->nomor_rekening}"];
                    }))
                    ->required(),
            ])
            ->action(function (array $data) {
                $sekolah = Auth::user()->sekolah;
                $paket = Paket::find($data['paket_id']);
                
                Tagihan::create([
                    'sekolah_id' => $sekolah->id,
                    'paket_id' => $paket->id,
                    'rekening_id' => $data['rekening_id'],
                    'jumlah_bayar' => $paket->harga,
                    'status' => 'pending',
                ]);
                
                Notification::make()->success()->title('Invoice Berhasil Dibuat')->send();
                $this->redirect(\App\Filament\Resources\TagihanResource::getUrl('index'));
            });
    }
}
