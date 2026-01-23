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

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Jurnal Mengajar Saya';
    protected static ?string $slug = 'jurnal-guru';
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
        // Filter: Hanya tampilkan jurnal milik user yang sedang login (kecuali Super Admin/Admin Sekolah mau lihat semua)
        // Untuk strict privacy guru:
        if (auth()->check() && auth()->user()->peran === 'guru') {
            $query->where('user_id', auth()->id());
        }
        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Pelajaran')
                    ->schema([
                        Select::make('kelas_id')
                            ->relationship('kelas', 'nama_kelas')
                            ->required()
                            ->label('Kelas'), // Bisa diedit admin, tapi di android otomatis

                        TextInput::make('mata_pelajaran')
                            ->required()
                            ->label('Mata Pelajaran'),

                        DatePicker::make('tanggal')
                            ->displayFormat('d F Y')
                            ->required(),

                        TextInput::make('jam_ke')->label('Jam Ke'),
                        TextInput::make('materi')->columnSpanFull()->label('Materi / Topik'),
                    ])->columns(2),

                // Repeater untuk Detail Siswa
                Section::make('Daftar Kehadiran Siswa')
                    ->schema([
                        Repeater::make('detail')
                            ->relationship()
                            ->schema([
                                Select::make('siswa_id')
                                    ->relationship('siswa', 'nama_lengkap')
                                    ->disabled() // Nama siswa readonly
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
                                    ->label('Status'),
                            ])
                            ->columns(2)
                            ->addable(false) // Data siswa otomatis dari Android
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
                TextColumn::make('tanggal')->date('d M Y')->sortable(),
                TextColumn::make('jam_ke')->label('Jam'),
                TextColumn::make('kelas.nama_kelas')->weight('bold')->searchable(),
                TextColumn::make('mata_pelajaran')->searchable(),
                TextColumn::make('materi')->limit(30),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
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
