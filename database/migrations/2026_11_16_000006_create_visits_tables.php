<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Una fila por página vista (personas y rastreadores).
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->char('session_hash', 64)->nullable();
            $table->string('visitor_id', 40)->nullable();
            $table->boolean('is_entry')->default(false);
            $table->string('path', 255);
            $table->string('route', 80)->nullable();
            $table->unsignedSmallInteger('status')->default(200);
            $table->string('referrer_host', 190)->nullable();
            $table->string('referrer', 500)->nullable();
            $table->string('channel', 20);
            $table->string('source_name', 60)->nullable();
            $table->string('utm_source', 100)->nullable();
            $table->string('utm_medium', 100)->nullable();
            $table->string('utm_campaign', 100)->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('country', 2)->nullable();
            $table->string('language', 10)->nullable();
            $table->string('device', 12)->nullable();
            $table->string('browser', 30)->nullable();
            $table->string('os', 20)->nullable();
            $table->boolean('is_bot')->default(false);
            $table->boolean('is_ai_bot')->default(false);
            $table->string('bot_name', 60)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['is_bot', 'created_at']);
            $table->index(['channel', 'created_at']);
            $table->index('session_hash');
        });

        // Clics, desplazamiento y tiempo en página (solo con consentimiento).
        Schema::create('visit_events', function (Blueprint $table) {
            $table->id();
            $table->char('session_hash', 64)->nullable();
            $table->string('visitor_id', 40)->nullable();
            $table->string('type', 20);
            $table->string('label', 150)->nullable();
            $table->string('target', 500)->nullable();
            $table->string('path', 255)->nullable();
            $table->unsignedInteger('value')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_events');
        Schema::dropIfExists('visits');
    }
};
