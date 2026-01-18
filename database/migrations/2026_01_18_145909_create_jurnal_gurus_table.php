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
        Schema::create('jurnal_guru', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sekolah_id')->constrained('sekolah')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Guru yang mengajar
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();

            $table->string('mata_pelajaran');
            $table->text('materi'); // Materi yang diajarkan
            $table->date('tanggal');
            $table->string('jam_ke'); // Contoh: "1-2"

            // Rekap Singkat (Diisi otomatis nanti oleh sistem)
            $table->integer('hadir')->default(0);
            $table->integer('izin')->default(0);
            $table->integer('sakit')->default(0);
            $table->integer('alpha')->default(0);

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurnal_gurus');
    }
};
