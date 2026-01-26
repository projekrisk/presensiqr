<?php

namespace App\Filament\Resources\JurnalGuruResource\Pages;

use App\Filament\Resources\JurnalGuruResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use App\Models\JurnalGuru;

class ListJurnalGurus extends ListRecords
{
    protected static string $resource = JurnalGuruResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            // --- 1. EXPORT HARIAN ---
            Actions\Action::make('export_harian')
                ->label('Export Harian')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->form([
                    Select::make('kelas_id')
                        ->label('Kelas')
                        ->options(fn () => \App\Models\Kelas::where('sekolah_id', auth()->user()->sekolah_id)->pluck('nama_kelas', 'id'))
                        ->searchable()
                        ->required(),
                    DatePicker::make('tanggal')
                        ->label('Tanggal')
                        ->default(now())
                        ->required(),
                ])
                ->action(function (array $data) {
                    $query = JurnalGuru::query()
                        ->where('kelas_id', $data['kelas_id'])
                        ->whereDate('tanggal', $data['tanggal']);
                    
                    if (auth()->user()->peran === 'guru') {
                        $query->where('user_id', auth()->id());
                    }

                    $jurnal = $query->first();

                    if (!$jurnal) {
                        \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title('Data Tidak Ditemukan')
                            ->body('Tidak ada data absensi untuk kelas dan tanggal tersebut.')
                            ->send();
                        return;
                    }

                    return redirect()->route('export.jurnal', $jurnal->id);
                }),

            // --- 2. EXPORT BULANAN ---
            Actions\Action::make('export_bulanan')
                ->label('Export Bulanan')
                ->icon('heroicon-o-calendar')
                ->color('info')
                ->form([
                    Select::make('bulan')
                        ->options([
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                        ])
                        ->default(date('n'))
                        ->required(),
                    Select::make('tahun')
                        ->options(array_combine(range(date('Y'), date('Y')-5), range(date('Y'), date('Y')-5)))
                        ->default(date('Y'))
                        ->required(),
                ])
                ->action(function (array $data) {
                    return redirect()->route('export.jurnal.bulanan', [
                        'bulan' => $data['bulan'],
                        'tahun' => $data['tahun']
                    ]);
                }),
        ];
    }
}