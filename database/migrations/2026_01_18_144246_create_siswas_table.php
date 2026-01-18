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
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();

            // Relasi Multi-tenant & Kelas
            $table->foreignId('sekolah_id')->constrained('sekolah')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();

            // Data Pribadi
            $table->string('nisn')->nullable(); // Boleh kosong jika belum ada
            $table->string('nis')->nullable();  // Nomor Induk Sekolah
            $table->string('nama_lengkap');
            $table->string('jenis_kelamin'); // L/P

            // Keamanan QR
            $table->text('qr_code_data')->nullable()->comment('Isi string terenkripsi untuk QR');

            // Foto Profil (Untuk Validasi Wajah di Android)
            $table->string('foto')->nullable();

            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswas');
    }
};
