<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PerangkatResource\Pages;
use App\Models\Perangkat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

// Import Komponen
use Filament\Forms\Components\Card;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;

class PerangkatResource extends Resource
{
    protected static ?string $model = Perangkat::class;

    protected static ?string $navigationIcon = 'heroicon-o-device-tablet';
    protected static ?string $navigationLabel = 'Manajemen Perangkat';
    protected static ?string $slug = 'perangkat';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Select::make('sekolah_id')
                            ->relationship('sekolah', 'nama_sekolah')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->hidden(fn () => auth()->user()->sekolah_id !== null)
                            ->label('Milik Sekolah'),

                        TextInput::make('nama_device')
                            ->required()
                            ->placeholder('Contoh: Tablet Pos Satpam')
                            ->label('Nama Perangkat'),

                        TextInput::make('device_id_hash')
                            ->label('Device ID (Salin dari HP)')
                            ->required()
                            ->unique(ignoreRecord: true)
                            // --- TAMBAHKAN BARIS INI ---
                            // Agar saat admin paste ID asli, sistem otomatis mengubahnya jadi Hash sebelum simpan
                            ->dehydrateStateUsing(fn ($state) => hash('sha256', $state)) 
                            // ---------------------------
                            ->helperText('Paste ID yang muncul di layar HP. Sistem akan otomatis mengenkripsinya.'),

                        Toggle::make('status_aktif')
                            ->label('Status Aktif')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger'),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sekolah.nama_sekolah')
                    ->searchable()
                    ->sortable()
                    ->label('Sekolah'),

                TextColumn::make('nama_device')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('device_id_hash')
                    ->limit(20) // Potong tampilan agar tidak terlalu panjang
                    ->copyable() // Bisa dicopy admin
                    ->tooltip('Klik untuk menyalin Hash ID'),

                ToggleColumn::make('status_aktif')
                    ->label('Aktif'),
            ])
            ->filters([
                //
            ])
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPerangkats::route('/'),
            'create' => Pages\CreatePerangkat::route('/create'),
            'edit' => Pages\EditPerangkat::route('/{record}/edit'),
        ];
    }
}
