<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TagihanResource\Pages;
use App\Models\Tagihan;
use App\Models\Paket;
use App\Models\Rekening;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class TagihanResource extends Resource
{
    protected static ?string $model = Tagihan::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Tagihan & Langganan';
    protected static ?int $navigationSort = 2;

    // --- FITUR: Sembunyikan dari Sidebar Admin Sekolah ---
    // Admin Sekolah mengakses tagihan lewat menu "Member Area"
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->sekolah_id === null;
    }

    // --- FITUR: Badge Notifikasi Count ---
    public static function getNavigationBadge(): ?string
    {
        if (!auth()->check()) return null;

        $user = auth()->user();
        
        // Query dasar: Status pending
        $query = static::getModel()::where('status', 'pending');

        // Jika user adalah Admin Sekolah, filter berdasarkan sekolah_id milik user
        // Jika Super Admin, tampilkan semua pending
        if ($user->sekolah_id) {
            $query->where('sekolah_id', $user->sekolah_id);
        }

        $count = $query->count();
        
        return $count > 0 ? (string) $count : null;
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Bagian Kiri: Detail Pembelian
                Forms\Components\Section::make('Detail Pembelian')
                    ->schema([
                        Select::make('paket_id')
                            ->label('Pilih Paket Langganan')
                            ->options(Paket::where('is_active', true)->pluck('nama_paket', 'id'))
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => 
                                $set('jumlah_bayar', Paket::find($state)?->harga ?? 0)
                            )
                            ->disabled(fn ($record) => $record !== null), // Tidak bisa ubah paket setelah dibuat

                        TextInput::make('jumlah_bayar')
                            ->prefix('Rp')
                            ->numeric()
                            ->readOnly()
                            ->required(),

                        Select::make('rekening_id')
                            ->label('Transfer ke Bank')
                            ->options(Rekening::where('is_active', true)->get()->mapWithKeys(function ($item) {
                                return [$item->id => "{$item->nama_bank} - {$item->nomor_rekening} a.n {$item->atas_nama}"];
                            }))
                            ->required()
                            ->disabled(fn ($record) => $record && $record->status !== 'pending'),
                    ])->columns(1),

                // Bagian Kanan: Bukti Bayar
                Forms\Components\Section::make('Konfirmasi Pembayaran')
                    ->schema([
                        FileUpload::make('bukti_bayar')
                            ->label('Upload Bukti Transfer')
                            ->disk('uploads')
                            ->directory('bukti-bayar')
                            ->image()
                            ->imageEditor()
                            ->disabled(fn ($record) => $record && $record->status === 'paid'), // Kunci jika sudah lunas
                        
                        Placeholder::make('status_text')
                            ->label('Status Pembayaran')
                            ->content(fn ($record) => $record ? strtoupper($record->status) : 'DRAFT'),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomor_invoice')->searchable()->sortable(),
                TextColumn::make('sekolah.nama_sekolah')
                    ->label('Sekolah')
                    ->hidden(fn () => auth()->check() && auth()->user()->sekolah_id !== null), // Hide jika user sekolah
                TextColumn::make('paket.nama_paket')->label('Paket'),
                TextColumn::make('jumlah_bayar')->money('IDR'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                ImageColumn::make('bukti_bayar')->disk('uploads')->circular(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                // Tombol Upload (Untuk Sekolah)
                Tables\Actions\EditAction::make()
                    ->label('Upload Bukti')
                    ->hidden(fn ($record) => $record->status === 'paid'),

                // Tombol Approve (KHUSUS SUPER ADMIN)
                Action::make('approve')
                    ->label('Terima Pembayaran')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => auth()->user()->sekolah_id === null && $record->status === 'pending')
                    ->action(function (Tagihan $record) {
                        // 1. Update Status Tagihan
                        $record->update([
                            'status' => 'paid',
                            'tgl_lunas' => now(),
                        ]);

                        // 2. Perpanjang Langganan Sekolah
                        $sekolah = $record->sekolah;
                        $paket = $record->paket;
                        
                        // Hitung tanggal baru
                        $currentExpiry = $sekolah->tgl_berakhir_langganan ? Carbon::parse($sekolah->tgl_berakhir_langganan) : now();
                        
                        // Jika sudah expired, mulai dari sekarang. Jika belum, tambah dari tanggal expired.
                        if ($currentExpiry->isPast()) {
                            $newExpiry = now()->addDays($paket->durasi_hari);
                        } else {
                            $newExpiry = $currentExpiry->addDays($paket->durasi_hari);
                        }

                        // Update Sekolah
                        $sekolah->update([
                            'paket_langganan' => 'pro', // Atau sesuaikan logic nama paket
                            'tgl_berakhir_langganan' => $newExpiry,
                            'status_aktif' => true
                        ]);

                        Notification::make()->success()->title('Pembayaran Diterima & Paket Aktif')->send();
                    }),
                
                // Tombol Tolak
                Action::make('reject')
                    ->label('Tolak')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => auth()->user()->sekolah_id === null && $record->status === 'pending')
                    ->action(fn (Tagihan $record) => $record->update(['status' => 'rejected'])),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTagihans::route('/'),
            'create' => Pages\CreateTagihan::route('/create'),
            'edit' => Pages\EditTagihan::route('/{record}/edit'),
        ];
    }
}