<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('email')->unique();
            // $table->string('password');
            $table->string('phone')->nullable();
            $table->string('company')->nullable();

            $table->string('wa_sender_number')->nullable()->comment('WhatsApp number used to send messages');
            $table->string('wa_api_key')->nullable()->comment('API key for WhatsApp gateway');
            $table->string('wa_api_url')->nullable()->comment('WhatsApp gateway URL');

            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');

            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('pincode', 10)->nullable();

            $table->rememberToken();
            $table->timestamps();
            // $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
