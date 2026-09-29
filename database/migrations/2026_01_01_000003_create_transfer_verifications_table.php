<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfer_verifications', function (Blueprint $table) {
            $table->string('id', 30)->primary();
            $table->unsignedBigInteger('citizen_id')->nullable();
            $table->string('nama_warga');
            $table->string('blok', 10)->nullable();
            $table->string('no_rumah', 10)->nullable();
            $table->string('periode', 100);
            $table->decimal('nominal', 15, 2);
            $table->string('metode', 100);
            $table->string('rekening_tujuan', 150)->nullable();
            $table->string('tanggal_transfer', 100);
            $table->string('no_referensi', 100)->nullable();
            $table->text('bukti_url')->nullable();
            $table->enum('status', ['Menunggu Verifikasi', 'Terverifikasi', 'Ditolak'])->default('Menunggu Verifikasi');
            $table->text('catatan_warga')->nullable();
            $table->text('catatan_bendahara')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_verifications');
    }
};
