<?php

    namespace App\Console\Commands;

    use Illuminate\Console\Command;
    use App\Models\Tagihan;

    class AutoCancelTagihan extends Command
    {
        // Nama perintah yang akan dijalankan scheduler
        protected $signature = 'tagihan:autocancel';

        // Deskripsi perintah
        protected $description = 'Membatalkan tagihan yang pending lebih dari 24 jam secara otomatis';

        public function handle()
        {
            // Cari tagihan pending yang dibuat lebih dari 24 jam lalu
            $batasWaktu = now()->subHours(24);

            $jumlah = Tagihan::where('status', 'pending')
                ->where('created_at', '<', $batasWaktu)
                ->update([
                    'status' => 'cancelled',
                    'catatan_admin' => 'Dibatalkan otomatis oleh sistem (Expired 24 Jam).'
                ]);

            $this->info("Berhasil membatalkan {$jumlah} tagihan kadaluarsa.");
        }
    }