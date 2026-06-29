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
        Schema::table('clients', function (Blueprint $table) {
            $table->string('chatbot_slug')->unique()->nullable()->after('company');
            $table->boolean('chatbot_enabled')->default(false)->after('chatbot_slug');
            $table->boolean('whatsapp_enabled')->default(false)->after('chatbot_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['chatbot_slug', 'chatbot_enabled', 'whatsapp_enabled']);
        });
    }
};
