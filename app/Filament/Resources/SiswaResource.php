<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiswaResource\Pages;
use App\Models\Siswa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

// Import Komponen Form
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;

// Import Komponen Table
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Actions\Action; // Import Action kustom
use Illuminate\Contracts\View\View; // Import View
use Illuminate\Database\Eloquent\Builder;

class SiswaResource extends Resource
{
    protected static ?string $model = Siswa::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Data Siswa';

    protected static ?string $slug = 'siswa';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Data Akademik')
                    ->schema([
                        Select::make('sekolah_id')
                            ->relationship('sekolah', 'nama_sekolah')
                            ->searchable()
                            ->preload()
                            ->label('Siswa')
                            ->required()
                            ->hidden(fn () => auth()->user()->sekolah_id !== null)
                            ->reactive(),

                        // Sorting Kelas (Pendek dulu, baru Panjang)
                        Select::make('kelas_id')
                            ->relationship(
                                name: 'kelas',
                                titleAttribute: 'nama_kelas',
                                modifyQueryUsing: fn (Builder $query) => $query->orderByRaw('LENGTH(nama_kelas)')->orderBy('nama_kelas')
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('nisn')
                            ->label('NISN')
                            ->numeric(),
                        TextInput::make('nis')
                            ->label('NIS Lokal'),
                    ])->columns(2),

                Section::make('Data Pribadi')
                    ->schema([
                        TextInput::make('nama_lengkap')
                            ->required(),
                        Select::make('jenis_kelamin')
                            ->options([
                                'L' => 'Laki-laki',
                                'P' => 'Perempuan'
                            ])
                            ->required(),
                        FileUpload::make('foto')
                            ->disk('uploads')
                            ->directory('siswa-foto')
                            ->image()
                            ->imageEditor(),

                        // Field QR Code (Read Only / Hidden)
                        TextInput::make('qr_code_data')
                            ->label('Kode QR (Otomatis)')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),

                        Toggle::make('status_aktif')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('foto')
                    ->disk('uploads')
                    ->circular(),
                TextColumn::make('nisn')
                    ->searchable(),
                TextColumn::make('nama_lengkap')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->sortable(),
                TextColumn::make('sekolah.nama_sekolah')
                    ->label('Sekolah')
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('status_aktif'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                // --- TOMBOL BARU: LIHAT QR ---
                Action::make('qr_code')
                    ->label('QR Code')
                    ->icon('heroicon-o-qr-code')
                    ->color('success')
                    ->modalHeading('Kartu QR Siswa')
                    ->modalContent(fn ($record): View => view(
                        'filament.actions.qr-code', 
                        ['record' => $record]
                    ))
                    ->modalSubmitAction(false) // Hilangkan tombol submit karena cuma view
                    ->modalCancelAction(fn ($action) => $action->label('Tutup')),
                // -----------------------------
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSiswas::route('/'),
            'create' => Pages\CreateSiswa::route('/create'),
            'edit' => Pages\EditSiswa::route('/{record}/edit'),
        ];
    }
}
