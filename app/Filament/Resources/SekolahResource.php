<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SekolahResource\Pages;
use App\Models\Sekolah;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

// Import komponen Form
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;

// Import komponen Table
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\ToggleColumn;

class SekolahResource extends Resource
{
    protected static ?string $model = Sekolah::class;

    // Icon Sidebar
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    // Label Sidebar
    protected static ?string $navigationLabel = 'Data Sekolah';

    // URL Slug (Agar URL menjadi /admin/sekolah bukan /admin/sekolahs)
    protected static ?string $slug = 'sekolah';

    // Urutan menu (1 = paling atas)
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3) // Layout 3 kolom
                ->schema([
                    // Kolom Kiri: Identitas (Lebar 2)
                    Section::make('Identitas Sekolah')
                        ->columnSpan(2)
                        ->schema([
                            TextInput::make('npsn')
                                ->label('NPSN')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->numeric(),
                            TextInput::make('nama_sekolah')
                                ->label('Nama Sekolah')
                                ->required(),
                            TextInput::make('email_admin')
                                ->label('Email Kontak Admin')
                                ->email(),
                            TextInput::make('alamat')
                                ->label('Alamat Lengkap'),
                        ]),

                    // Kolom Kanan: Langganan & Logo (Lebar 1)
                    Section::make('Status Langganan')
                        ->columnSpan(1)
                        ->schema([
                            FileUpload::make('logo')
                                ->label('Logo Sekolah')
                                ->disk('uploads') // Gunakan disk khusus tanpa symlink
                                ->directory('sekolah-logo') // Folder di dalam uploads/
                                ->image()
                                ->imageEditor(), 
                            Select::make('paket_langganan')
                                ->options([
                                    'free' => 'Gratis (Trial)',
                                    'basic' => 'Basic (Menengah)',
                                    'pro' => 'Pro (Tahunan/Lengkap)',
                                ])
                                ->required()
                                ->default('free'),
                            DatePicker::make('tgl_berakhir_langganan')
                                ->label('Berlaku Sampai'),
                            Toggle::make('status_aktif')
                                ->label('Status Aktif')
                                ->default(true)
                                ->onColor('success')
                                ->offColor('danger'),
                        ]),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->disk('public/uploads') // Wajib set disk di sini juga agar gambar tampil
                    ->circular(),
                TextColumn::make('npsn')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama_sekolah')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('paket_langganan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'free' => 'gray',
                        'basic' => 'warning',
                        'pro' => 'success',
                    }),
                TextColumn::make('tgl_berakhir_langganan')
                    ->date()
                    ->label('Expired')
                    ->sortable(),
                ToggleColumn::make('status_aktif')
                    ->label('Aktif'),
            ])
            ->filters([
                // Filter tambahan bisa ditaruh sini
            ])
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSekolahs::route('/'),
            'create' => Pages\CreateSekolah::route('/create'),
            'edit' => Pages\EditSekolah::route('/{record}/edit'),
        ];
    }
}
