<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaketResource\Pages;
use App\Models\Paket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;

class PaketResource extends Resource
{
    protected static ?string $model = Paket::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Manajemen Paket';
    protected static ?int $navigationSort = 1;

    // Hanya Super Admin yang bisa akses
    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()->sekolah_id === null;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nama_paket')
                    ->required()
                    ->maxLength(255),
                TextInput::make('harga')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
                TextInput::make('durasi_hari')
                    ->required()
                    ->numeric()
                    ->suffix('Hari')
                    ->helperText('Contoh: 365 untuk 1 tahun'),
                Textarea::make('deskripsi')
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_paket')->searchable(),
                TextColumn::make('harga')->money('IDR')->sortable(),
                TextColumn::make('durasi_hari')->numeric()->sortable(),
                ToggleColumn::make('is_active')->label('Aktif'),
            ])
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPakets::route('/'),
            'create' => Pages\CreatePaket::route('/create'),
            'edit' => Pages\EditPaket::route('/{record}/edit'),
        ];
    }
}
