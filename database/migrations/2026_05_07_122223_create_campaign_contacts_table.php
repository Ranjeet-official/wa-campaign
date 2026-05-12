<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('campaign_contacts', function (Blueprint $table) {

            $table->id();

            $table->foreignId('campaign_id')
                ->constrained()
                ->onDelete('cascade');

            $table->string('name')->nullable();

            $table->string('phone');

            $table->enum('status', [
                'pending',
                'sent',
                'failed'
            ])->default('pending');

            $table->text('response')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_contacts');
    }
};
