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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;

class JurnalGuruResource extends Resource
{
    protected static ?string $model = JurnalGuru::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Jurnal Mengajar Saya';
    protected static ?string $slug = 'jurnal-guru';
    protected static ?int $navigationSort = 1; // Paling atas untuk Guru

    // --- FILTER QUERY: HANYA LIHAT PUNYA SENDIRI ---
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Filter: Hanya tampilkan jurnal milik user yang sedang login
        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('kelas_id')
                    ->relationship('kelas', 'nama_kelas')
                    ->required()
                    ->label('Kelas'),
                
                TextInput::make('mata_pelajaran')
                    ->required()
                    ->placeholder('Contoh: Matematika'),
                
                DatePicker::make('tanggal')
                    ->default(now())
                    ->required(),
                
                TextInput::make('materi')
                    ->required()
                    ->columnSpanFull()
                    ->label('Materi / Topik'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal')->date('d M Y'),
                TextColumn::make('jam_ke')->label('Jam'),
                TextColumn::make('kelas.nama_kelas')->weight('bold'),
                TextColumn::make('mata_pelajaran'),
                TextColumn::make('materi')->limit(30),
            ])
            ->filters([])
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
            'index' => Pages\ListJurnalGurus::route('/'),
            'create' => Pages\CreateJurnalGuru::route('/create'),
            'edit' => Pages\EditJurnalGuru::route('/{record}/edit'),
        ];
    }
}
