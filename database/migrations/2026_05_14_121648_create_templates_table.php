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

            $table->string('name');

            $table->enum('type', ['text', 'image', 'video', 'document'])
                ->default('text');

            $table->enum('category', ['MARKETING', 'UTILITY', 'AUTHENTICATION'])
                ->default('MARKETING');

            $table->string('language')->default('en_US');

            $table->longText('message');

            $table->string('media_file')->nullable();

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
