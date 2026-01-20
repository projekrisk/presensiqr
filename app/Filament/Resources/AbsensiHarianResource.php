<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AbsensiHarianResource\Pages;
use App\Models\AbsensiHarian;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class AbsensiHarianResource extends Resource
{
    protected static ?string $model = AbsensiHarian::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Absensi Gerbang (Kiosk)';
    protected static ?string $slug = 'absensi-harian';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('siswa_id')
                    ->relationship('siswa', 'nama_lengkap')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->nama_lengkap} ({$record->nisn}) - {$record->kelas->nama_kelas}")
                    ->searchable(['nama_lengkap', 'nisn'])
                    ->required(),
                DatePicker::make('tanggal')
                    ->required(),
                TimePicker::make('jam_masuk'),
                TimePicker::make('jam_pulang'),
                Select::make('status')
                    ->options([
                        'Hadir' => 'Hadir',
                        'Telat' => 'Telat',
                        'Izin' => 'Izin',
                        'Sakit' => 'Sakit',
                        'Alpha' => 'Alpha',
                    ]),
                TextInput::make('keterangan'),
                
                // Input Sekolah ID (Disembunyikan jika Admin Sekolah)
                Select::make('sekolah_id')
                    ->relationship('sekolah', 'nama_sekolah')
                    ->required()
                    ->hidden(fn () => auth()->check() && auth()->user()->sekolah_id !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal')->date('d M Y')->sortable(),
                TextColumn::make('siswa.nama_lengkap')->searchable()->weight('bold')->label('Nama Siswa'),
                TextColumn::make('siswa.kelas.nama_kelas')->label('Kelas')->sortable(),
                TextColumn::make('jam_masuk')->time('H:i')->placeholder('-'),
                TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'Hadir' => 'success',
                    'Telat' => 'warning',
                    'Alpha' => 'danger',
                    'Sakit' => 'info',
                    'Izin' => 'info',
                    default => 'gray',
                }),
                TextColumn::make('sumber')->label('Via'),
                // Sembunyikan kolom sekolah di tabel jika user adalah Admin Sekolah
                TextColumn::make('sekolah.nama_sekolah')
                    ->label('Sekolah')
                    ->sortable()
                    ->hidden(fn () => auth()->check() && auth()->user()->sekolah_id !== null),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('hari_ini')
                    ->query(fn (Builder $query): Builder => $query->whereDate('tanggal', now()))
                    ->label('Hari Ini')
                    ->default(),
                SelectFilter::make('status')
                    ->options([
                        'Hadir' => 'Hadir',
                        'Telat' => 'Telat',
                        'Sakit' => 'Sakit',
                        'Izin' => 'Izin',
                        'Alpha' => 'Alpha',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAbsensiHarians::route('/'),
            'create' => Pages\CreateAbsensiHarian::route('/create'),
            'edit' => Pages\EditAbsensiHarian::route('/{record}/edit'),
        ];
    }
}
