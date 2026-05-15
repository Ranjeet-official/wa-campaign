<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->id();

            // Client relation (multi-tenant support)
            $table->foreignId('client_id')
                ->nullable()
                ->constrained()
                ->onDelete('cascade');

            // Template basic info
            $table->string('name');
            $table->string('language')->default('en_US');

            // WhatsApp Meta fields
            $table->string('category')->nullable(); // MARKETING / UTILITY / AUTH
            $table->string('meta_template_id')->nullable();

            // Message content
            $table->longText('message');

            // Dynamic variables like {{1}}, {{2}}
            $table->json('variables')->nullable();

            // Approval status from Meta
            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
