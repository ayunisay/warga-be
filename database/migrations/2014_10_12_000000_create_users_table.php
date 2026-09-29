<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->text('nik')->nullable(); // Terenkripsi AES-256 (UU PDP)
            $table->string('nik_bindex', 64)->nullable()->index(); // Blind Index untuk pencarian cepat
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('Warga');
            $table->string('jabatan')->nullable();
            $table->text('telepon')->nullable(); // Terenkripsi AES-256 (UU PDP)
            $table->string('avatar_initial', 10)->nullable();
            $table->string('status')->default('Aktif');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};