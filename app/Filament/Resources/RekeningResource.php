<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RekeningResource\Pages;
use App\Models\Rekening;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;

class RekeningResource extends Resource
{
    protected static ?string $model = Rekening::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Rekening Bank';
    protected static ?int $navigationSort = 3;

    // Hanya Super Admin yang bisa akses
    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()->sekolah_id === null;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nama_bank')
                    ->label('Nama Bank')
                    ->placeholder('Contoh: BCA, Mandiri')
                    ->required(),
                TextInput::make('nomor_rekening')
                    ->label('Nomor Rekening')
                    ->numeric()
                    ->required(),
                TextInput::make('atas_nama')
                    ->label('Atas Nama')
                    ->required(),
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
                TextColumn::make('nama_bank')->searchable(),
                TextColumn::make('nomor_rekening')->copyable(),
                TextColumn::make('atas_nama')->searchable(),
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
            'index' => Pages\ListRekenings::route('/'),
            'create' => Pages\CreateRekening::route('/create'),
            'edit' => Pages\EditRekening::route('/{record}/edit'),
        ];
    }
}
