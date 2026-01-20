<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KelasResource\Pages;
use App\Models\Kelas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class KelasResource extends Resource
{
    protected static ?string $model = Kelas::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Data Kelas';
    protected static ?string $slug = 'kelas';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('sekolah_id')
                    ->relationship('sekolah', 'nama_sekolah')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Sekolah')
                    // Sembunyikan jika user adalah Admin Sekolah
                    ->hidden(fn () => auth()->check() && auth()->user()->sekolah_id !== null),
                    
                TextInput::make('nama_kelas')
                    ->required()
                    ->placeholder('Contoh: X RPL 1')
                    ->label('Nama Kelas'),
                TextInput::make('tingkat')
                    ->numeric()
                    ->placeholder('10, 11, atau 12')
                    ->label('Tingkat'),
                TextInput::make('jurusan')
                    ->placeholder('RPL, TKJ, dll')
                    ->label('Jurusan'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sekolah.nama_sekolah')
                    ->searchable()
                    ->sortable()
                    ->label('Sekolah')
                    // Sembunyikan kolom sekolah di tabel jika user adalah Admin Sekolah
                    ->hidden(fn () => auth()->check() && auth()->user()->sekolah_id !== null),
                    
                TextColumn::make('nama_kelas')->searchable()->weight('bold'),
                TextColumn::make('jurusan')->searchable(),
                TextColumn::make('tingkat')->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListKelas::route('/'),
            'create' => Pages\CreateKelas::route('/create'),
            'edit' => Pages\EditKelas::route('/{record}/edit'),
        ];
    }
}