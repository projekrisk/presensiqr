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
        Schema::table('users', function (Blueprint $table) {
            // Menambahkan kolom sekolah_id setelah kolom id
            // Nullable karena Super Admin SaaS tidak punya sekolah
            $table->foreignId('sekolah_id')->nullable()->after('id')->constrained('sekolah')->nullOnDelete();

            // Menambahkan peran user (role)
            $table->string('peran')->default('guru')->after('password'); // admin_sekolah, guru

            // Menambahkan NIP/NUPTK untuk login selain email (opsional)
            $table->string('nomor_induk')->nullable()->after('email');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
