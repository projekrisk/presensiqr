<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiswaResource\Pages;
use App\Models\Siswa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Actions\Action;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class SiswaResource extends Resource
{
    protected static ?string $model = Siswa::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Data Siswa';
    protected static ?string $slug = 'siswa';
    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        if ($user->sekolah_id === null) return true;
        return $user->peran === 'admin_sekolah';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Data Pribadi')
                    ->schema([
                        FileUpload::make('foto')
                            ->disk('uploads')
                            ->directory('siswa-foto')
                            ->image()
                            ->imageEditor()
                            ->avatar()
                            ->alignCenter()
                            ->columnSpanFull(),

                        Grid::make(2)->schema([
                            TextInput::make('nama_lengkap')->label('Nama Lengkap')->required(),
                            Select::make('jenis_kelamin')
                                ->label('Jenis Kelamin')
                                ->options(['L' => 'Laki-laki', 'P' => 'Perempuan'])
                                ->required(),
                        ]),

                        TextInput::make('qr_code_data')
                            ->label('Kode QR (Generate Otomatis)')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                    ]),

                Section::make('Data Akademik')
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('kelas_id')
                                ->relationship(
                                    name: 'kelas',
                                    titleAttribute: 'nama_kelas',
                                    modifyQueryUsing: fn (Builder $query) => $query->orderByRaw('LENGTH(nama_kelas)')->orderBy('nama_kelas')
                                )
                                ->searchable()
                                ->preload()
                                ->required()
                                ->label('Kelas'),
                            TextInput::make('nisn')->label('NISN')->numeric()->required(),
                            TextInput::make('nis')->label('NIS Lokal'),
                        ]),
                        Select::make('sekolah_id')
                            ->relationship('sekolah', 'nama_sekolah')
                            ->required()
                            ->hidden(fn () => auth()->check() && auth()->user()->sekolah_id !== null),
                        Toggle::make('status_aktif')->label('Status Aktif')->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('foto')->disk('uploads')->circular(),
                TextColumn::make('nisn')->searchable(),
                TextColumn::make('nama_lengkap')->searchable()->weight('bold'),
                TextColumn::make('kelas.nama_kelas')->label('Kelas')->sortable(),
                TextColumn::make('sekolah.nama_sekolah')
                    ->label('Sekolah')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->hidden(fn () => auth()->check() && auth()->user()->sekolah_id !== null),
                ToggleColumn::make('status_aktif'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('qr_code')
                    ->label('QR')
                    ->icon('heroicon-o-qr-code')
                    ->color('success')
                    ->modalContent(fn ($record): View => view('filament.actions.qr-code', ['record' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelAction(fn ($action) => $action->label('Tutup')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    // --- DOWNLOAD QR ZIP ---
                    Tables\Actions\BulkAction::make('download_qr')
                        ->label('Download QR Code (ZIP)')
                        ->icon('heroicon-o-archive-box-arrow-down')
                        ->color('warning')
                        ->action(function ($records) {
                            $ids = $records->pluck('id')->implode(',');
                            return redirect()->route('download.qr.zip', ['ids' => $ids]);
                        })
                        ->deselectRecordsAfterCompletion()
                ]),
            ]);
    }

    public static function getRelations(): array { return []; }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSiswas::route('/'),
            'create' => Pages\CreateSiswa::route('/create'),
            'edit' => Pages\EditSiswa::route('/{record}/edit'),
        ];
    }
}