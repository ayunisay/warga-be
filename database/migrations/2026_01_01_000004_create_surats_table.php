<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surats', function (Blueprint $table) {
            $table->string('id', 30)->primary();
            $table->string('no_surat', 100);
            $table->string('jenis_surat', 150);
            $table->string('pemohon', 150);
            $table->text('nik'); // Terenkripsi AES-256 (UU PDP)
            $table->string('nik_bindex', 64)->nullable()->index(); // Blind Index SHA-256
            $table->text('alamat')->nullable();
            $table->text('tujuan')->nullable();
            $table->string('tanggal_pengajuan', 50);
            $table->enum('status', ['Menunggu Verifikasi', 'Disetujui', 'Ditolak', 'Selesai'])->default('Menunggu Verifikasi');
            $table->enum('urgensi', ['Biasa', 'Sedang', 'Tinggi'])->default('Biasa');
            $table->text('catatan')->nullable();
            $table->text('catatan_penolakan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surats');
    }
};