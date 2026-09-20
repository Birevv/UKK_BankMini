<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurnal_akuntansi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_transaksi');
            $table->string('kode_akun', 10); // '101' atau '201'
            $table->enum('posisi', ['debet', 'kredit']);
            $table->bigInteger('jumlah');
            $table->timestamps();

            $table->foreign('id_transaksi')->references('id')->on('transaksi')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurnal_akuntansi');
    }
};
