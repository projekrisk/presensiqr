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
        Schema::create('absensi_harian', function (Blueprint $table) {
            $table->id();

            // Relasi
            $table->foreignId('sekolah_id')->constrained('sekolah')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();

            // Data Waktu
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();

            // Status Kehadiran
            // Hadir = Tepat Waktu, Telat = Terlambat, Izin/Sakit = Input Manual
            $table->enum('status', ['Hadir', 'Telat', 'Izin', 'Sakit', 'Alpha'])->default('Alpha');

            // Sumber Data (Penting untuk Audit)
            $table->string('sumber')->default('Mesin'); // 'Mesin', 'Manual', 'Android Guru'

            // Foto Bukti (Selfie saat scan - Opsional)
            $table->string('foto_bukti')->nullable();

            // Keterangan tambahan (misal: "Sakit Demam")
            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_harians');
    }
};
