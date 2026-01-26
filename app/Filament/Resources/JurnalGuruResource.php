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
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;

class JurnalGuruResource extends Resource
{
    protected static ?string $model = JurnalGuru::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check'; 
    protected static ?string $navigationLabel = 'Absensi Kelas (Guru)'; 
    protected static ?string $slug = 'absensi-kelas';
    protected static ?int $navigationSort = 1; 

    // --- FILTER HAK AKSES ---
    public static function canViewAny(): bool
    {
        $user = auth()->user();
        if ($user->sekolah_id === null) return true; // Super Admin
        return in_array($user->peran, ['guru', 'admin_sekolah']);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        // Guru hanya melihat datanya sendiri
        if (auth()->check() && auth()->user()->peran === 'guru') {
            $query->where('user_id', auth()->id());
        }
        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Info Absensi')
                    ->schema([
                        Select::make('kelas_id')
                            ->relationship('kelas', 'nama_kelas')
                            ->required()
                            ->disabled() 
                            ->dehydrated() 
                            ->label('Kelas'),

                        DatePicker::make('tanggal')
                            ->displayFormat('d F Y')
                            ->required(),
                    ])->columns(2),

                // Repeater untuk Detail Siswa
                Section::make('Rekap Kehadiran Siswa')
                    ->schema([
                        Repeater::make('detail')
                            ->relationship()
                            ->schema([
                                Select::make('siswa_id')
                                    ->relationship('siswa', 'nama_lengkap')
                                    ->disabled() 
                                    ->dehydrated()
                                    ->label('Nama Siswa')
                                    ->required(),

                                Select::make('status')
                                    ->options([
                                        'Hadir' => 'Hadir',
                                        'Sakit' => 'Sakit',
                                        'Izin' => 'Izin',
                                        'Alpha' => 'Alpha',
                                    ])
                                    ->required()
                                    ->label('Status'),
                            ])
                            ->columns(2)
                            ->addable(false) 
                            ->deletable(false)
                            ->reorderable(false)
                            ->label('Detail Siswa'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal')->date('d M Y')->sortable()->label('Tanggal'),
                TextColumn::make('kelas.nama_kelas')->weight('bold')->searchable()->label('Kelas'),
                
                // Menampilkan ringkasan kehadiran
                TextColumn::make('detail_count')
                     ->counts('detail')
                     ->label('Total Siswa'),
                     
                TextColumn::make('hadir')->label('Hadir')->color('success'),
                TextColumn::make('sakit')->label('Sakit')->color('info'),
                TextColumn::make('izin')->label('Izin')->color('warning'),
                TextColumn::make('alpha')->label('Alpha')->color('danger'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                // ... filters ...
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Lihat'),
                
                // --- TOMBOL EXPORT EXCEL ---
                Tables\Actions\Action::make('export_excel')
                    ->label('Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->url(fn (JurnalGuru $record) => route('export.jurnal', $record->id))
                    ->openUrlInNewTab(),
                // ---------------------------

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJurnalGurus::route('/'),
            'create' => Pages\CreateJurnalGuru::route('/create'),
            'edit' => Pages\EditJurnalGuru::route('/{record}/edit'),
        ];
    }
}