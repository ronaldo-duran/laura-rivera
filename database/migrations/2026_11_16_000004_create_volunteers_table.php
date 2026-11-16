<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('volunteers', function (Blueprint $table) {
            $table->id();
            $table->string('document_type', 5);
            $table->string('document_number', 20);
            $table->string('first_names', 100);
            $table->string('last_names', 100);
            $table->string('preferred_name', 60)->nullable();
            $table->string('treatment', 20);
            $table->string('email', 190);
            $table->string('phone', 20);
            $table->string('neighborhood', 80)->nullable();
            $table->string('help_type', 60)->nullable();
            $table->timestamp('terms_accepted_at');
            $table->string('terms_version', 20);
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->string('source', 120)->nullable();
            $table->timestamps();

            $table->unique(['document_type', 'document_number']);
            $table->index('email');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('volunteers');
    }
};
