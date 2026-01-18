<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Siswa;
use App\Models\AbsensiHarian;
use Carbon\Carbon;

class MarkAlphaOtomatis extends Command
{
    // Nama perintah yang akan diketik di terminal
    protected $signature = 'absensi:mark-alpha';

    // Deskripsi perintah
    protected $description = 'Menandai siswa yang belum absen hingga jam ini sebagai Alpha';

    public function handle()
    {
        $today = Carbon::today()->toDateString();
        $this->info("Memulai proses Auto-Alpha untuk tanggal: $today");

        // 1. Cari siswa aktif yang BELUM punya data absensi hari ini
        // Kita pakai whereDoesntHave agar query ringan
        $siswaBelumAbsen = Siswa::where('status_aktif', true)
            ->whereDoesntHave('absensi', function ($query) use ($today) {
                $query->whereDate('tanggal', $today);
            })
            ->get();

        $count = 0;

        // 2. Loop dan tandai Alpha
        foreach ($siswaBelumAbsen as $siswa) {
            AbsensiHarian::create([
                'sekolah_id' => $siswa->sekolah_id, // Ambil ID sekolah siswa
                'siswa_id'   => $siswa->id,
                'tanggal'    => $today,
                'status'     => 'Alpha',
                'sumber'     => 'Sistem Otomatis',
                'keterangan' => 'Tidak melakukan scan hingga batas waktu (Auto System).',
            ]);

            $count++;
        }

        $this->info("Selesai! $count siswa ditandai Alpha.");
    }
}
