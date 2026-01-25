<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder; // Import Placeholder
use Filament\Forms\Components\Hidden;      // Import Hidden
use Filament\Notifications\Notification;
use App\Models\Paket;
use App\Models\Rekening;
use App\Models\Tagihan;
use Illuminate\Support\Facades\Auth;

class MemberPage extends Page implements HasForms, HasActions, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationLabel = 'Member Area';
    protected static ?string $title = 'Status Keanggotaan';
    protected static ?string $slug = 'member-area';
    protected static string $view = 'filament.pages.member-page';
    protected static ?int $navigationSort = 10;

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check() && Auth::user()->peran === 'admin_sekolah';
    }

    public function mount(): void
    {
        if (Auth::user()->peran !== 'admin_sekolah') {
            abort(403);
        }
    }

    // --- TABEL RIWAYAT TAGIHAN ---
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Tagihan::query()->where('sekolah_id', Auth::user()->sekolah_id)->latest()
            )
            ->columns([
                TextColumn::make('nomor_invoice')->label('No. Invoice')->searchable(),
                TextColumn::make('paket.nama_paket')->label('Paket'),
                TextColumn::make('jumlah_bayar')->money('IDR')->label('Total'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')->date('d M Y')->label('Tanggal'),
                ImageColumn::make('bukti_bayar')->disk('uploads')->circular(),
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('bayar')
                    ->label('Bayar / Upload')
                    ->icon('heroicon-o-credit-card')
                    ->url(fn (Tagihan $record) => \App\Filament\Resources\TagihanResource::getUrl('edit', ['record' => $record]))
                    ->visible(fn (Tagihan $record) => $record->status === 'pending'),
            ]);
    }

    // --- ACTION: UPGRADE PAKET (DIRECT) ---
    public function upgradeAction(): Action
    {
        // Otomatis ambil paket berbayar pertama (Premium Tahunan)
        $paketPremium = Paket::where('harga', '>', 0)->first();

        return Action::make('upgrade')
            ->label('Upgrade Paket')
            ->color('primary')
            ->icon('heroicon-o-sparkles')
            ->modalHeading('Upgrade ke Akun Premium')
            ->modalDescription('Dapatkan akses penuh selama 1 tahun dengan prioritas dukungan teknis.')
            ->modalSubmitActionLabel('Buat Tagihan')
            ->form([
                // Tampilkan Info Paket (Read Only)
                Placeholder::make('info_paket')
                    ->label('Paket Pilihan')
                    ->content(fn () => $paketPremium ? "{$paketPremium->nama_paket} - Rp " . number_format($paketPremium->harga, 0, ',', '.') : 'Paket Tidak Tersedia'),

                // Hidden Input untuk ID Paket
                Hidden::make('paket_id')
                    ->default($paketPremium?->id),
                    
                // Pilihan Bank
                Select::make('rekening_id')
                    ->label('Metode Pembayaran (Transfer Bank)')
                    ->options(Rekening::where('is_active', true)->get()->mapWithKeys(function ($item) {
                        return [$item->id => "{$item->nama_bank} - {$item->nomor_rekening} (a.n {$item->atas_nama})"];
                    }))
                    ->required()
                    ->native(false),
            ])
            ->action(function (array $data) use ($paketPremium) {
                if (!$paketPremium) {
                    Notification::make()->danger()->title('Paket tidak ditemukan')->send();
                    return;
                }

                $sekolah = Auth::user()->sekolah;
                
                Tagihan::create([
                    'sekolah_id' => $sekolah->id,
                    'paket_id' => $data['paket_id'],
                    'rekening_id' => $data['rekening_id'],
                    'jumlah_bayar' => $paketPremium->harga,
                    'status' => 'pending',
                ]);
                
                Notification::make()->success()->title('Invoice Berhasil Dibuat')->send();
                
                // Refresh halaman untuk melihat invoice baru di tabel
                return redirect()->route('filament.admin.pages.member-area');
            });
    }
}