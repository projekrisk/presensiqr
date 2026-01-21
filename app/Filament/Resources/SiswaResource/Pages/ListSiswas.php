<?php

namespace App\Filament\Resources\SiswaResource\Pages;

use App\Filament\Resources\SiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage; // PENTING: Untuk fix path file
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Kelas;
use App\Models\Siswa;

class ListSiswas extends ListRecords
{
    protected static string $resource = SiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            // --- TOMBOL IMPORT (Di Header Halaman) ---
            Actions\Action::make('import_excel')
                ->label('Import Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->form([
                    FileUpload::make('file_excel')
                        ->label('File Excel (.xlsx)')
                        ->disk('local') // Simpan di storage/app/temp-import
                        ->directory('temp-import')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->required(),
                ])
                ->modalDescription('Pastikan format sesuai template. Jika NISN sudah ada, data akan diperbarui.')
                ->extraModalFooterActions([
                    Actions\Action::make('download_template')
                        ->label('Download Template')
                        ->url(route('download.template.siswa'), shouldOpenInNewTab: true)
                        ->color('gray'),
                ])
                ->action(function (array $data) {
                    // PERBAIKAN PATH FILE: Gunakan Storage facade untuk mendapatkan path absolut yang valid
                    // Ini mengatasi error "File does not exist"
                    $filePath = Storage::disk('local')->path($data['file_excel']);

                    // Validasi eksistensi file
                    if (!file_exists($filePath)) {
                        Notification::make()->danger()->title('File tidak ditemukan di server')->send();
                        return;
                    }

                    // Baca Excel
                    try {
                        $rows = Excel::toArray(new class implements \Maatwebsite\Excel\Concerns\ToArray {
                            public function array(array $array) { return $array; }
                        }, $filePath);
                    } catch (\Exception $e) {
                        Notification::make()->danger()->title('Gagal membaca file Excel')->body($e->getMessage())->send();
                        return;
                    }

                    if (empty($rows) || empty($rows[0])) {
                        Notification::make()->danger()->title('File kosong atau format salah')->send();
                        return;
                    }

                    $sheet1 = $rows[0];
                    $successCount = 0;
                    $sekolahId = Auth::user()->sekolah_id; 

                    foreach ($sheet1 as $index => $row) {
                        if ($index === 0) continue; // Skip Header

                        $nama = $row[0] ?? null;
                        $jk = $row[1] ?? 'L';
                        $nisn = $row[2] ?? null;
                        $nis = $row[3] ?? null;
                        $namaKelas = $row[4] ?? null;

                        if (!$nama || !$nisn || !$namaKelas) continue;

                        // Validasi Kelas
                        $kelas = Kelas::where('nama_kelas', $namaKelas)
                            ->where('sekolah_id', $sekolahId)
                            ->first();

                        if (!$kelas) {
                            Notification::make()
                                ->danger()
                                ->title("Gagal baris ke-" . ($index+1))
                                ->body("Kelas '$namaKelas' tidak ditemukan. Buat kelas dulu.")
                                ->send();
                            continue;
                        }

                        // Simpan / Update
                        Siswa::updateOrCreate(
                            ['nisn' => $nisn, 'sekolah_id' => $sekolahId],
                            [
                                'nama_lengkap' => $nama,
                                'jenis_kelamin' => strtoupper($jk),
                                'nis' => $nis,
                                'kelas_id' => $kelas->id,
                                'status_aktif' => true,
                            ]
                        );
                        $successCount++;
                    }

                    if ($successCount > 0) {
                        Notification::make()->success()->title("Berhasil import $successCount siswa.")->send();
                    }
                    
                    // Bersihkan file temp
                    @unlink($filePath);
                }),
        ];
    }
}