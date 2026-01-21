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
        Schema::create('tagihan', function (Blueprint $table) {
            $table->id();
            // Relasi
            $table->foreignId('sekolah_id')->constrained('sekolah')->cascadeOnDelete();
            $table->foreignId('paket_id')->constrained('paket');
            $table->foreignId('rekening_id')->nullable()->constrained('rekening'); // Rekening tujuan transfer

            // Info Transaksi
            $table->string('nomor_invoice')->unique(); // INV-20240101-001
            $table->decimal('jumlah_bayar', 15, 2);
            $table->string('status')->default('pending'); // pending, paid, rejected, cancelled
            $table->string('bukti_bayar')->nullable(); // Path gambar
            $table->text('catatan_admin')->nullable(); // Jika ditolak kenapa?

            $table->timestamp('tgl_bayar')->nullable();
            $table->timestamp('tgl_lunas')->nullable();

            $table->timestamps();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tagihans');
    }
};
