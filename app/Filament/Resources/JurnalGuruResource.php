<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JurnalGuruResource\Pages;
use App\Models\JurnalGuru;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;

class JurnalGuruResource extends Resource
{
    protected static ?string $model = JurnalGuru::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check'; 
    protected static ?string $navigationLabel = 'Absensi Kelas (Guru)'; 
    protected static ?string $slug = 'absensi-kelas';
    protected static ?int $navigationSort = 1; 

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        if ($user->sekolah_id === null) return true; 
        return in_array($user->peran, ['guru', 'admin_sekolah']);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        if (auth()->check() && auth()->user()->peran === 'guru') {
            $query->where('user_id', auth()->id());
        }
        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Info Absensi')->schema([
                Select::make('kelas_id')
                    ->relationship('kelas', 'nama_kelas')
                    ->required()
                    ->disabled() 
                    ->dehydrated() 
                    ->label('Kelas'),
                DatePicker::make('tanggal')
                    ->displayFormat('d F Y')
                    ->required(),
            ])->columns(2),

            Section::make('Rekap Kehadiran Siswa')->schema([
                Repeater::make('detail')
                    ->relationship()
                    ->schema([
                        Select::make('siswa_id')
                            ->relationship('siswa', 'nama_lengkap')
                            ->disabled() 
                            ->dehydrated()
                            ->label('Nama Siswa')
                            ->required(),
                        Select::make('status')
                            ->options(['Hadir'=>'Hadir','Sakit'=>'Sakit','Izin'=>'Izin','Alpha'=>'Alpha'])
                            ->required()
                            ->label('Status'),
                    ])->columns(2)
                    ->addable(false) 
                    ->deletable(false)
                    ->reorderable(false)
                    ->label('Detail Siswa'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal')->date('d M Y')->sortable()->label('Tanggal'),
                TextColumn::make('kelas.nama_kelas')->weight('bold')->searchable()->label('Kelas'),
                TextColumn::make('detail_count')->counts('detail')->label('Total Siswa'),
                TextColumn::make('hadir')->label('Hadir')->color('success'),
                TextColumn::make('sakit')->label('Sakit')->color('info'),
                TextColumn::make('izin')->label('Izin')->color('warning'),
                TextColumn::make('alpha')->label('Alpha')->color('danger'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            // --- HEADER ACTIONS (TOMBOL EXPORT DI ATAS) ---
            ->headerActions([
                // 1. EXPORT HARIAN (Pindahan dari Row)
                Tables\Actions\Action::make('export_harian')
                    ->label('Export Harian (Per Kelas)')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->form([
                        Select::make('kelas_id')
                            ->label('Kelas')
                            ->options(fn () => \App\Models\Kelas::where('sekolah_id', auth()->user()->sekolah_id)->pluck('nama_kelas', 'id'))
                            ->searchable()
                            ->required(),
                        DatePicker::make('tanggal')
                            ->label('Tanggal')
                            ->default(now())
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $query = JurnalGuru::query()
                            ->where('kelas_id', $data['kelas_id'])
                            ->whereDate('tanggal', $data['tanggal']);
                        
                        // Jika Guru, hanya cari punya sendiri
                        if (auth()->user()->peran === 'guru') {
                            $query->where('user_id', auth()->id());
                        }

                        $jurnal = $query->first();

                        if (!$jurnal) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Data Tidak Ditemukan')
                                ->body('Tidak ada data absensi untuk kelas dan tanggal tersebut.')
                                ->send();
                            return;
                        }

                        return redirect()->route('export.jurnal', $jurnal->id);
                    }),

                // 2. EXPORT BULANAN
                Tables\Actions\Action::make('export_bulanan')
                    ->label('Export Rekap Bulanan')
                    ->icon('heroicon-o-calendar')
                    ->color('info')
                    ->form([
                        Select::make('bulan')
                            ->options([
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                            ])
                            ->default(date('n'))
                            ->required(),
                        Select::make('tahun')
                            ->options(array_combine(range(date('Y'), date('Y')-5), range(date('Y'), date('Y')-5)))
                            ->default(date('Y'))
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        return redirect()->route('export.jurnal.bulanan', [
                            'bulan' => $data['bulan'],
                            'tahun' => $data['tahun']
                        ]);
                    })
            ])
            // -------------------------------------
            ->actions([
                Tables\Actions\EditAction::make()->label('Lihat'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJurnalGurus::route('/'),
            'create' => Pages\CreateJurnalGuru::route('/create'),
            'edit' => Pages\EditJurnalGuru::route('/{record}/edit'),
        ];
    }
}