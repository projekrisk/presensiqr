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
use Filament\Forms\Components\TextInput;
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

    // --- FILTER HAK AKSES ---
    public static function canViewAny(): bool
    {
        // Bisa dilihat oleh Guru dan Admin Sekolah (Operator tidak perlu)
        $user = auth()->user();
        if ($user->sekolah_id === null) return true; // Super Admin
        return in_array($user->peran, ['guru', 'admin_sekolah']);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        // Filter: Hanya tampilkan data milik user yang sedang login
        if (auth()->check() && auth()->user()->peran === 'guru') {
            $query->where('user_id', auth()->id());
        }
        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Data Pertemuan Kelas')
                    ->schema([
                        Select::make('kelas_id')
                            ->relationship('kelas', 'nama_kelas')
                            ->required()
                            ->disabled() // Di android otomatis, di admin web mungkin perlu enable jika create manual
                            ->dehydrated() // Agar tetap terkirim meski disabled
                            ->label('Kelas'),

                        TextInput::make('mata_pelajaran')
                            ->required()
                            ->label('Mata Pelajaran'),

                        DatePicker::make('tanggal')
                            ->displayFormat('d F Y')
                            ->required(),

                        TextInput::make('jam_ke')->label('Jam Pelajaran Ke'),
                        
                        TextInput::make('materi')
                            ->columnSpanFull()
                            ->label('Catatan / Materi (Opsional)'),
                    ])->columns(2),

                // Repeater untuk Detail Siswa
                Section::make('Rekap Kehadiran Siswa')
                    ->description('Daftar siswa yang diabsen melalui Aplikasi Android')
                    ->schema([
                        Repeater::make('detail')
                            ->relationship()
                            ->schema([
                                Select::make('siswa_id')
                                    ->relationship('siswa', 'nama_lengkap')
                                    ->disabled() // Nama siswa readonly
                                    ->dehydrated()
                                    ->label('Nama Siswa')
                                    ->required(),

                                Select::make('status')
                                    ->options([
                                        'Hadir' => 'Hadir',
                                        'Sakit' => 'Sakit',
                                        'Izin' => 'Izin',
                                        'Alpha' => 'Alpha',
                                    ])
                                    ->required()
                                    ->label('Status Kehadiran')
                                    ->colors([
                                         'success' => 'Hadir',
                                         'warning' => 'Izin',
                                         'info' => 'Sakit',
                                         'danger' => 'Alpha',
                                    ]),
                            ])
                            ->columns(2)
                            ->addable(false) // Tidak boleh tambah siswa manual di sini
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
                TextColumn::make('jam_ke')->label('Jam Ke'),
                TextColumn::make('kelas.nama_kelas')->weight('bold')->searchable()->label('Kelas'),
                TextColumn::make('mata_pelajaran')->searchable()->label('Mapel'),
                
                // Menampilkan ringkasan kehadiran di tabel depan
                TextColumn::make('detail_count')
                     ->counts('detail')
                     ->label('Total Siswa'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make()->label('Lihat Detail'),
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
