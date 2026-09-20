<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nasabah_id');
            $table->string('jenis_transaksi');
            $table->bigInteger('nominal');
            $table->string('nama_petugas')->default('Teller');
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->foreign('nasabah_id')->references('id')->on('nasabah')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
