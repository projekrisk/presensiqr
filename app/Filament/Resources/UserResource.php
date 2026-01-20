<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    // Label menu diatur di sini
    protected static ?string $navigationLabel = 'Data Guru & Staf';
    
    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('sekolah_id')
                    ->relationship('sekolah', 'nama_sekolah')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Sekolah'),
                
                TextInput::make('name')
                    ->required()
                    ->label('Nama Guru'),
                
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                
                TextInput::make('password')
                    ->password()
                    // Hanya wajib saat create (user baru)
                    ->required(fn (string $operation): bool => $operation === 'create')
                    // Hash password sebelum simpan agar aman
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    // Jangan update password jika form kosong saat edit
                    ->dehydrated(fn ($state) => filled($state)),
                
                Select::make('peran')
                    ->options([
                        'guru' => 'Guru',
                        'admin_sekolah' => 'Admin Sekolah',
                    ])
                    ->default('guru')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('sekolah.nama_sekolah')
                    ->label('Sekolah')
                    ->sortable(),
                TextColumn::make('peran')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'guru' => 'info',
                        'admin_sekolah' => 'warning',
                        default => 'gray',
                    }),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
