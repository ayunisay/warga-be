<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rt_infos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('app_name');
            $table->string('rt', 10);
            $table->string('rw', 10);
            $table->string('kelurahan');
            $table->string('kecamatan');
            $table->string('kota');
            $table->string('provinsi');
            $table->string('kode_pos', 10);
            $table->text('alamat_sekretariat');
            $table->string('ketua_rt');
            $table->string('sekretaris');
            $table->string('bendahara');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rt_infos');
    }
};
