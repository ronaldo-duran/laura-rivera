<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 64)->unique();
            $table->unsignedBigInteger('amount_in_cents');
            $table->string('currency', 3)->default('COP');
            // CREATED (aún no pagó), PENDING, APPROVED, DECLINED, VOIDED, ERROR
            $table->string('status', 12)->default('CREATED');
            $table->string('wompi_transaction_id', 64)->nullable()->index();
            $table->string('payment_method_type', 30)->nullable();
            $table->string('full_name', 150);
            $table->string('document_type', 5);
            $table->string('document_number', 20);
            $table->string('email', 190);
            $table->string('phone', 20)->nullable();
            $table->timestamp('terms_accepted_at');
            $table->string('ip_address', 45)->nullable();
            $table->string('source', 120)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('status_updated_at')->nullable();
            $table->json('last_event')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('document_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
