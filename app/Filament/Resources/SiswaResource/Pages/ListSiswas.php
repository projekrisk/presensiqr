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
use ZipArchive;

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
                    $missingClasses = []; // Array untuk menampung kelas yang hilang

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
                            // Kumpulkan kelas yang tidak ditemukan (hindari duplikat di notif)
                            if (!in_array($namaKelas, $missingClasses)) {
                                $missingClasses[] = $namaKelas;
                            }
                            continue; // Skip baris ini
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

                    // Tampilkan notifikasi error hanya sekali (summary)
                    if (!empty($missingClasses)) {
                         Notification::make()
                            ->danger()
                            ->title('Gagal Mengimpor Beberapa Data')
                            ->body('Kelas berikut tidak ditemukan di sistem: ' . implode(', ', $missingClasses) . '. Silakan buat kelas tersebut terlebih dahulu pada menu Data Kelas.')
                            ->persistent() // Notifikasi tidak hilang otomatis agar user bisa baca
                            ->send();
                    }
                    
                    // Bersihkan file temp
                    @unlink($filePath);
                }),

            // --- 2. IMPORT FOTO (ZIP) ---
            // ... (Kode Import Foto tetap sama seperti sebelumnya) ...
             Actions\Action::make('import_foto')
                ->label('Import Foto (ZIP)')
                ->icon('heroicon-o-photo')
                ->color('info')
                ->form([
                    FileUpload::make('file_zip')
                        ->label('File ZIP berisi Foto')
                        ->helperText('Nama file foto harus sesuai NISN (cth: 12345.jpg).')
                        ->disk('local')
                        ->directory('temp-import')
                        ->acceptedFileTypes(['application/zip', 'application/x-zip-compressed'])
                        ->required(),
                ])
                ->action(function (array $data) {
                    $zipPath = Storage::disk('local')->path($data['file_zip']);
                    $zip = new ZipArchive;
                    $sekolahId = Auth::user()->sekolah_id;
                    $successCount = 0;

                    if ($zip->open($zipPath) === TRUE) {
                        for ($i = 0; $i < $zip->numFiles; $i++) {
                            $filename = $zip->getNameIndex($i);
                            if (str_contains($filename, '/') || str_starts_with($filename, '.')) continue;

                            $extension = pathinfo($filename, PATHINFO_EXTENSION);
                            if (!in_array(strtolower($extension), ['jpg', 'jpeg', 'png'])) continue;

                            $nisn = pathinfo($filename, PATHINFO_FILENAME);

                            $siswa = Siswa::where('nisn', $nisn)
                                ->where('sekolah_id', $sekolahId)
                                ->first();

                            if ($siswa) {
                                $stream = $zip->getFromIndex($i);
                                $newFilename = 'siswa-foto/' . $nisn . '_' . time() . '.' . $extension;
                                Storage::disk('uploads')->put($newFilename, $stream);
                                $siswa->update(['foto' => $newFilename]);
                                $successCount++;
                            }
                        }
                        $zip->close();
                        Notification::make()->success()->title("Berhasil mengimpor $successCount foto siswa.")->send();
                    } else {
                        Notification::make()->danger()->title('Gagal membuka file ZIP.')->send();
                    }
                    @unlink($zipPath);
                }),
        ];
    }
}