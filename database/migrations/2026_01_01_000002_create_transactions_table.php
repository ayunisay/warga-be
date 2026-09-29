<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->string('id', 30)->primary();
            $table->string('tanggal', 50);
            $table->enum('kategori', ['Pemasukan', 'Pengeluaran']);
            $table->string('sumber');
            $table->decimal('nominal', 15, 2);
            $table->text('keterangan')->nullable();
            $table->string('petugas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
