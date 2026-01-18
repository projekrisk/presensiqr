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
        Schema::create('sekolah', function (Blueprint $table) {
            $table->id();
            // Data Identitas Sekolah
            $table->string('npsn')->unique()->comment('Nomor Pokok Sekolah Nasional');
            $table->string('nama_sekolah');
            $table->text('alamat')->nullable();
            $table->string('logo')->nullable();

            // Data Kontak Penanggung Jawab (Opsional, untuk backup)
            $table->string('email_admin')->nullable();
            $table->string('nama_admin')->nullable();

            // Data Langganan (SaaS Logic)
            $table->string('paket_langganan')->default('free'); // free, basic, pro
            $table->date('tgl_mulai_langganan')->nullable();
            $table->date('tgl_berakhir_langganan')->nullable();
            $table->boolean('status_aktif')->default(true);

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sekolahs');
    }
};
