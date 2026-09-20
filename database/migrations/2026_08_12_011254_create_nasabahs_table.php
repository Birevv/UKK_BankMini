<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nasabah', function (Blueprint $table) {
            $table->id();
            $table->string('no_rekening', 20)->unique();
            $table->string('nis', 20)->unique();
            $table->string('nama_siswa', 100);
            $table->string('kelas', 50);
            $table->string('jurusan', 100);
            $table->string('username', 50)->unique();
            $table->string('password');
            $table->string('pin_keamanan');
            $table->bigInteger('saldo')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nasabah');
    }
};
