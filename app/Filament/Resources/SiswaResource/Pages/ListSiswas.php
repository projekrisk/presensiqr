<?php

namespace App\Filament\Resources\SiswaResource\Pages;

use App\Filament\Resources\SiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage; 
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Kelas;
use App\Models\Siswa;
use ZipArchive; // Import ZipArchive

class ListSiswas extends ListRecords
{
    protected static string $resource = SiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            // --- 1. IMPORT DATA EXCEL ---
            Actions\Action::make('import_excel')
                ->label('Import Excel')
                ->icon('heroicon-o-document-text')
                ->color('success')
                ->form([
                    FileUpload::make('file_excel')
                        ->label('File Excel (.xlsx)')
                        ->disk('local') 
                        ->directory('temp-import')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->required(),
                ])
                ->modalDescription('Format: Nama, JK, NISN, NIS, Kelas. NISN Wajib Unik.')
                ->extraModalFooterActions([
                    Actions\Action::make('download_template')
                        ->label('Download Template')
                        ->url(route('download.template.siswa'), shouldOpenInNewTab: true)
                        ->color('gray'),
                ])
                ->action(function (array $data) {
                    $filePath = Storage::disk('local')->path($data['file_excel']);

                    if (!file_exists($filePath)) {
                        Notification::make()->danger()->title('File tidak ditemukan')->send();
                        return;
                    }

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
                        if ($index === 0) continue; 

                        $nama = $row[0] ?? null;
                        $jk = $row[1] ?? 'L';
                        $nisn = $row[2] ?? null;
                        $nis = $row[3] ?? null;
                        $namaKelas = $row[4] ?? null;

                        if (!$nama || !$nisn || !$namaKelas) continue;

                        $kelas = Kelas::where('nama_kelas', $namaKelas)
                            ->where('sekolah_id', $sekolahId)
                            ->first();

                        if (!$kelas) {
                            Notification::make()
                                ->danger()
                                ->title("Baris " . ($index+1) . " Gagal")
                                ->body("Kelas '$namaKelas' tidak ditemukan.")
                                ->send();
                            continue;
                        }

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
                        Notification::make()->success()->title("Berhasil import $successCount data siswa.")->send();
                    }
                    
                    @unlink($filePath);
                }),

            // --- 2. IMPORT FOTO (ZIP) ---
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
                            
                            // Abaikan folder atau file hidden (macOS __MACOSX)
                            if (str_contains($filename, '/') || str_starts_with($filename, '.')) continue;

                            // Ekstensi
                            $extension = pathinfo($filename, PATHINFO_EXTENSION);
                            if (!in_array(strtolower($extension), ['jpg', 'jpeg', 'png'])) continue;

                            // Ambil NISN dari nama file (tanpa ekstensi)
                            $nisn = pathinfo($filename, PATHINFO_FILENAME);

                            // Cari Siswa
                            $siswa = Siswa::where('nisn', $nisn)
                                ->where('sekolah_id', $sekolahId)
                                ->first();

                            if ($siswa) {
                                // Baca konten file dari ZIP
                                $stream = $zip->getFromIndex($i);
                                
                                // Generate nama unik baru untuk disimpan
                                $newFilename = 'siswa-foto/' . $nisn . '_' . time() . '.' . $extension;
                                
                                // Simpan ke disk 'uploads' (public/uploads)
                                Storage::disk('uploads')->put($newFilename, $stream);
                                
                                // Update database
                                $siswa->update(['foto' => $newFilename]);
                                $successCount++;
                            }
                        }
                        $zip->close();
                        
                        Notification::make()->success()
                            ->title("Berhasil mengimpor $successCount foto siswa.")
                            ->send();
                    } else {
                        Notification::make()->danger()
                            ->title('Gagal membuka file ZIP.')
                            ->send();
                    }
                    
                    @unlink($zipPath);
                }),
        ];
    }
}