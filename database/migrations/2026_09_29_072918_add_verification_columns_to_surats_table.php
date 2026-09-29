<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surats', function (Blueprint $table) {
            $table->string('verify_token', 64)->nullable()->unique()->after('status');
            $table->string('penandatangan', 150)->nullable()->default('Bpk. Bambang Pamungkas (Ketua RT 007)')->after('verify_token');
            $table->timestamp('disetujui_pada')->nullable()->after('penandatangan');
        });
    }

    public function down(): void
    {
        Schema::table('surats', function (Blueprint $table) {
            $table->dropColumn(['verify_token', 'penandatangan', 'disetujui_pada']);
        });
    }
};