<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('kategori');
            $table->string('tanggal', 50);
            $table->string('waktu', 100)->nullable();
            $table->string('lokasi', 150)->nullable();
            $table->string('icon', 50)->nullable();
            $table->string('color', 30)->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('status', 50)->default('Aktif');
            $table->string('prioritas', 50)->default('Rutin');
            $table->string('penulis', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
