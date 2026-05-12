<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');

            // Basic Info
            $table->string('full_name', 150);
            $table->string('whatsapp_number', 20);
            $table->string('email', 150)->nullable();
            $table->string('phone', 20)->nullable();

            // Personal Info
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();

            // Address
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('pincode', 10)->nullable();

            // Classification

            // Status
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active');

            $table->timestamps();

            // Unique: ek admin ke under same whatsapp number 2 baar nahi
            // $table->unique(['admin_id', 'whatsapp_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
