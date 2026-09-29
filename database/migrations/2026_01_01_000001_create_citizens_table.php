<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('citizens', function (Blueprint $table) {
            $table->id();
            // Data Sensitif Terenkripsi AES-256 (UU PDP No. 27/2022)
            $table->text('nik'); // Payload terenkripsi
            $table->string('nik_bindex', 64)->nullable()->index(); // Blind Index SHA-256
            $table->text('no_kk')->nullable(); // Payload terenkripsi
            $table->string('no_kk_bindex', 64)->nullable()->index(); // Blind Index SHA-256
            $table->string('nama');
            $table->string('jenis_kelamin');
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('agama')->nullable();
            $table->string('status_kawin')->nullable();
            $table->string('pekerjaan')->nullable();
            $table->string('alamat')->nullable();
            $table->string('blok', 10)->nullable();
            $table->string('no_rumah', 10)->nullable();
            $table->string('status_tinggal')->default('Tetap');
            $table->text('telepon')->nullable(); // Terenkripsi AES-256
            $table->string('status_iuran')->default('Lunas');
            $table->string('peran_keluarga')->default('Kepala Keluarga');
            $table->string('tanggal_daftar')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citizens');
    }
};