<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->unsignedBigInteger('citizen_id')->nullable();
            $table->string('nama_warga');
            $table->string('blok', 10)->nullable();
            $table->string('no_rumah', 10)->nullable();
            $table->string('periode', 100);
            $table->decimal('nominal', 15, 2);
            $table->string('payment_gateway', 30)->default('midtrans');
            $table->string('payment_type', 30);
            $table->string('bank', 20)->nullable();
            $table->string('va_number', 50)->nullable();
            $table->text('qr_string')->nullable();
            $table->text('qr_url')->nullable();
            $table->enum('status', ['pending', 'settlement', 'expire', 'cancel', 'failure'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->string('gateway_transaction_id', 100)->nullable();
            $table->string('signature_key', 255)->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();

            $table->index(['citizen_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
