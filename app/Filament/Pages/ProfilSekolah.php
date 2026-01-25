<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TimePicker;
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

    // Filter Akses: Hanya Admin Sekolah
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check() 
            && Auth::user()->sekolah_id !== null 
            && Auth::user()->peran === 'admin_sekolah';
    }

    public function mount(): void
    {
        // Keamanan Tambahan
        if (Auth::user()->peran !== 'admin_sekolah') {
            abort(403, 'Akses Ditolak. Halaman ini khusus Admin Sekolah.');
        }

        $sekolah = Auth::user()->sekolah;

        if ($sekolah) {
            $this->form->fill([
                // Data Identitas
                'nama_sekolah' => $sekolah->nama_sekolah,
                'npsn' => $sekolah->npsn,
                'alamat' => $sekolah->alamat,
                'email_admin' => $sekolah->email_admin,
                'logo' => $sekolah->logo,
                
                // Data Pengaturan Presensi
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
                        // TAB 1: Identitas
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
                        
                        // TAB 2: Pengaturan Presensi
                        // (Tab Paket Langganan dihapus dari sini untuk dipindah ke Sidebar Widget)
                        Tabs\Tab::make('Aturan Jam & Hari')
                            ->icon('heroicon-m-clock')
                            ->schema([
                                CheckboxList::make('hari_kerja')
                                    ->label('Hari Sekolah Aktif')
                                    ->options([
                                        'Senin' => 'Senin',
                                        'Selasa' => 'Selasa',
                                        'Rabu' => 'Rabu',
                                        'Kamis' => 'Kamis',
                                        'Jumat' => 'Jumat',
                                        'Sabtu' => 'Sabtu',
                                        'Minggu' => 'Minggu',
                                    ])
                                    ->columns(4)
                                    ->columnSpanFull()
                                    ->required(),

                                TimePicker::make('jam_mulai_absen')
                                    ->label('Buka Gerbang (Mulai Scan)')
                                    ->helperText('Siswa tidak bisa absen sebelum jam ini.')
                                    ->seconds(false)
                                    ->native(false)
                                    ->format('H:i')
                                    ->displayFormat('H:i')
                                    ->closeOnDateSelection()
                                    ->required(),

                                TimePicker::make('jam_masuk')
                                    ->label('Jam Masuk (Batas Telat)')
                                    ->helperText('Scan setelah jam ini dianggap TERLAMBAT.')
                                    ->seconds(false)
                                    ->native(false)
                                    ->format('H:i')
                                    ->displayFormat('H:i')
                                    ->closeOnDateSelection()
                                    ->required(),

                                TimePicker::make('jam_pulang')
                                    ->label('Jam Pulang Sekolah')
                                    ->helperText('Siswa baru bisa scan pulang setelah jam ini.')
                                    ->seconds(false)
                                    ->native(false)
                                    ->format('H:i')
                                    ->displayFormat('H:i')
                                    ->closeOnDateSelection()
                                    ->required(),
                            ])->columns(3),
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
                'hari_kerja' => $data['hari_kerja'],
                'jam_mulai_absen' => $data['jam_mulai_absen'],
                'jam_masuk' => $data['jam_masuk'],
                'jam_pulang' => $data['jam_pulang'],
            ]);
            
            Notification::make()->success()->title('Profil & Pengaturan Diperbarui')->send();
        }
    }
}