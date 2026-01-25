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

    // Hanya Admin Sekolah yang bisa lihat menu ini
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
                // Ambil tagihan milik sekolah ini saja
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
                // Aksi untuk Upload Bukti jika masih pending (Menggunakan URL resource tagihan)
                \Filament\Tables\Actions\Action::make('bayar')
                    ->label('Bayar / Upload')
                    ->icon('heroicon-o-credit-card')
                    ->url(fn (Tagihan $record) => \App\Filament\Resources\TagihanResource::getUrl('edit', ['record' => $record]))
                    ->visible(fn (Tagihan $record) => $record->status === 'pending'),
            ]);
    }

    // --- ACTION: UPGRADE PAKET ---
    public function upgradeAction(): Action
    {
        return Action::make('upgrade')
            ->label('Upgrade Paket')
            ->color('primary')
            ->icon('heroicon-o-sparkles')
            ->modalHeading('Pilih Paket Langganan')
            ->form([
                Select::make('paket_id')
                    ->label('Pilih Paket')
                    ->options(Paket::where('is_active', true)->where('harga', '>', 0)->pluck('nama_paket', 'id'))
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
                
                // Refresh halaman untuk melihat tabel terupdate
                return redirect()->route('filament.admin.pages.member-area');
            });
    }
}
```

---

### 4. Sembunyikan Menu Tagihan Lama (Opsional)

Agar tidak duplikat, Anda bisa menyembunyikan menu "Tagihan" dari sidebar untuk Admin Sekolah (biarkan hanya Super Admin yang melihatnya).

Buka `app/Filament/Resources/TagihanResource.php`, ubah `shouldRegisterNavigation`:

```php
    public static function shouldRegisterNavigation(): bool
    {
        // Hanya Super Admin yang lihat menu Tagihan di sidebar
        // Admin Sekolah lihat tagihan lewat menu "Member Area"
        return auth()->check() && auth()->user()->sekolah_id === null;
    }