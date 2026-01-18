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
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            // Relasi ke Sekolah (Wajib untuk Multi-tenant)
            $table->foreignId('sekolah_id')->constrained('sekolah')->cascadeOnDelete();

            $table->string('nama_kelas'); // Contoh: X RPL 1
            $table->string('tingkat')->nullable(); // 10, 11, 12
            $table->string('jurusan')->nullable(); // RPL, TKJ

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
