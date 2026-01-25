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
use Filament\Forms\Components\Placeholder; 
use Filament\Forms\Components\Hidden;      
use Filament\Notifications\Notification;
use App\Models\Paket;
use App\Models\Rekening;
use App\Models\Tagihan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon; // Import Carbon

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
                    ->formatStateUsing(fn (Tagihan $record) => 
                        ($record->status === 'pending' && $record->created_at->addHours(24)->isPast()) 
                            ? 'Kadaluwarsa' 
                            : ucfirst($record->status)
                    )
                    ->color(fn (Tagihan $record) => match (true) {
                        $record->status === 'pending' && $record->created_at->addHours(24)->isPast() => 'gray',
                        $record->status === 'pending' => 'warning',
                        $record->status === 'paid' => 'success',
                        $record->status === 'rejected' => 'danger',
                        default => 'gray',
                    }),

                // Kolom Tanggal Pembuatan (Dengan Timezone Jakarta)
                TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->timezone('Asia/Jakarta') // Fix Jam agar sesuai WIB
                    ->label('Dibuat Tanggal')
                    ->sortable(),

                // KOLOM INFORMASI / DEADLINE
                TextColumn::make('info_waktu')
                    ->label('Batas Waktu / Masa Aktif') // PERBAIKAN: Label harus statis, tidak boleh pakai fn($record)
                    ->getStateUsing(function (Tagihan $record) {
                        // KASUS 1: Sudah Lunas -> Tampilkan Kapan Paket Berakhir
                        if ($record->status === 'paid' && $record->tgl_lunas) {
                            return Carbon::parse($record->tgl_lunas)
                                ->addDays($record->paket->durasi_hari)
                                ->translatedFormat('d M Y');
                        }
                        
                        // KASUS 2: Pending -> Tampilkan Deadline Pembayaran (Created + 24 Jam)
                        if ($record->status === 'pending') {
                            $deadline = $record->created_at->addHours(24);
                            
                            if ($deadline->isPast()) {
                                return 'Invoice Kadaluwarsa';
                            }
                            
                            // Hitung sisa waktu
                            return $deadline->timezone('Asia/Jakarta')->format('d M Y H:i') 
                                . ' (' . $deadline->diffForHumans() . ')';
                        }
                        
                        // KASUS 3: Batal/Ditolak
                        return 'Tidak Berlaku';
                    })
                    ->color(fn (Tagihan $record) => match($record->status) {
                        'paid' => 'success',
                        'pending' => $record->created_at->addHours(24)->isPast() ? 'gray' : 'danger', // Merah agar diperhatikan
                        default => 'gray',
                    })
                    ->size(TextColumn\TextColumnSize::ExtraSmall)
                    ->wrap(), // Agar teks panjang turun ke bawah

                ImageColumn::make('bukti_bayar')->disk('uploads')->circular(),
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('bayar')
                    ->label(fn (Tagihan $record) => 
                        ($record->created_at->addHours(24)->isPast()) ? 'Kadaluwarsa' : 'Bayar / Upload'
                    )
                    ->icon(fn (Tagihan $record) => 
                        ($record->created_at->addHours(24)->isPast()) ? 'heroicon-o-x-circle' : 'heroicon-o-credit-card'
                    )
                    ->color(fn (Tagihan $record) => 
                        ($record->created_at->addHours(24)->isPast()) ? 'gray' : 'primary'
                    )
                    ->disabled(fn (Tagihan $record) => 
                        $record->created_at->addHours(24)->isPast()
                    )
                    ->url(fn (Tagihan $record) => 
                        $record->created_at->addHours(24)->isPast() ? null : \App\Filament\Resources\TagihanResource::getUrl('edit', ['record' => $record])
                    )
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
            ->modalSubmitActionLabel('Buat Tagihan')
            ->form([
                // Tampilkan Info Paket (Read Only)
                Placeholder::make('detail_paket')
                    ->hiddenLabel()
                    ->content(fn () => new \Illuminate\Support\HtmlString("
                        <div class='text-center p-6 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700'>
                            <p class='text-xs font-bold text-gray-500 uppercase tracking-widest mb-2'>Paket Pilihan</p>
                            <h2 class='text-2xl font-black text-primary-600 dark:text-primary-400'>
                                " . ($paketPremium ? $paketPremium->nama_paket : 'Tidak Tersedia') . "
                            </h2>
                            <div class='mt-2 flex justify-center items-baseline gap-1'>
                                <span class='text-4xl font-extrabold text-gray-900 dark:text-white'>
                                    Rp " . number_format($paketPremium?->harga ?? 0, 0, ',', '.') . "
                                </span>
                                <span class='text-sm text-gray-500'>/ Tahun</span>
                            </div>
                        </div>
                    ")),

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
                    ->native(false)
                    ->prefixIcon('heroicon-m-building-library'),

                // Panduan Langkah Pembayaran
                Placeholder::make('panduan')
                    ->hiddenLabel()
                    ->content(new \Illuminate\Support\HtmlString("
                        <div class='mt-2 p-4 bg-yellow-50 dark:bg-yellow-900/10 rounded-xl border border-yellow-200 dark:border-yellow-800'>
                            <h4 class='font-bold text-yellow-700 dark:text-yellow-500 flex items-center gap-2 mb-2'>
                                <svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'></path></svg>
                                Instruksi Pembayaran:
                            </h4>
                            <ol class='list-decimal list-inside text-sm text-gray-600 dark:text-gray-400 space-y-1 ml-1'>
                                <li>Pilih <strong>Bank Tujuan</strong> di atas.</li>
                                <li>Klik tombol <strong>Buat Tagihan</strong> untuk memproses invoice.</li>
                                <li>Lakukan transfer sejumlah nominal ke rekening tersebut.</li>
                                <li>Upload bukti transfer pada menu <strong>Riwayat Tagihan</strong> di bawah ini (Batas waktu 24 jam).</li>
                            </ol>
                        </div>
                    ")),
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
                
                Notification::make()->success()->title('Invoice Berhasil Dibuat')->body('Silakan upload bukti pembayaran pada tabel di bawah.')->send();
                
                // Refresh halaman untuk melihat invoice baru di tabel
                return redirect()->route('filament.admin.pages.member-area');
            });
    }
}