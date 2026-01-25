<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Paket;

class PaketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data Paket
        $data = [
            [
                'nama_paket' => 'Free Trial (4 Bulan)',
                'harga' => 0,
                'durasi_hari' => 152, // Sekitar 4-5 bulan efektif sekolah
                'deskripsi' => "Paket percobaan gratis untuk evaluasi sistem. \nFitur mencakup:\n- Akses Dashboard Admin\n- Aplikasi Android Guru & Kiosk\n- Manajemen Siswa & Absensi\n- Laporan Dasar",
                'is_active' => true,
            ],
            [
                'nama_paket' => 'Premium Tahunan',
                'harga' => 350000,
                'durasi_hari' => 365, // 1 Tahun
                'deskripsi' => "Solusi lengkap untuk operasional sekolah jangka panjang. \nFitur mencakup:\n- Semua fitur Free Trial\n- Prioritas Dukungan Teknis (Support)\n- Backup Data Otomatis\n- Export Laporan Lengkap (Excel/PDF)\n- Update Fitur Terbaru",
                'is_active' => true,
            ]
        ];

        foreach ($data as $item) {
            // Gunakan updateOrCreate agar tidak duplikat jika seeder dijalankan ulang
            Paket::updateOrCreate(
                ['nama_paket' => $item['nama_paket']], // Kunci pencarian
                $item // Data yang diupdate/create
            );
        }
    }
}