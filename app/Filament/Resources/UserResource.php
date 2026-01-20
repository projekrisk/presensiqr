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
use Illuminate\Database\Eloquent\Builder; // Import Builder

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static ?string $navigationLabel = 'Data Guru & Staf';
    
    protected static ?int $navigationSort = 9;

    // --- FITUR FILTER MANUAL (PENTING UNTUK USER) ---
    // Karena User tidak pakai Global Scope (HasSekolah) agar tidak Infinite Loop,
    // kita harus memfilternya di sini.
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->check() && auth()->user()->sekolah_id) {
            $query->where('sekolah_id', auth()->user()->sekolah_id);
        }

        return $query;
    }

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
                    ->hidden(fn () => auth()->check() && auth()->user()->sekolah_id !== null),
                
                TextInput::make('name')
                    ->required()
                    ->label('Nama Guru'),
                
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                
                TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
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
                TextColumn::make('name')->searchable()->weight('bold'),
                TextColumn::make('email')->searchable(),
                TextColumn::make('sekolah.nama_sekolah')
                    ->label('Sekolah')
                    ->sortable()
                    ->hidden(fn () => auth()->check() && auth()->user()->sekolah_id !== null),
                TextColumn::make('peran')->badge()->color(fn (string $state): string => match ($state) {
                    'guru' => 'info',
                    'admin_sekolah' => 'warning',
                    default => 'gray',
                }),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}