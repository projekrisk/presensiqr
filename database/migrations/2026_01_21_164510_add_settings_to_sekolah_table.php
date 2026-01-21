<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sekolah', function (Blueprint $table) {
            // Simpan hari aktif sebagai JSON (Contoh: ["Senin", "Selasa", ...])
            $table->json('hari_kerja')->nullable(); 

            // Jam mulai scan (06:30) - Sebelum ini tidak bisa absen
            $table->time('jam_mulai_absen')->default('06:00:00'); 

            // Jam masuk / Batas Telat (07:15) - Lewat ini status "Telat"
            $table->time('jam_masuk')->default('07:00:00'); 

            // Jam Pulang (16:00) - Sebelum ini tidak bisa absen pulang
            $table->time('jam_pulang')->default('16:00:00'); 
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sekolah', function (Blueprint $table) {
            //
        });
    }
};
