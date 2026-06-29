<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->longText('chatbot_prompt')->nullable()->after('chatbot_enabled');
            $table->longText('chatbot_knowledge_base')->nullable()->after('chatbot_prompt');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['chatbot_prompt', 'chatbot_knowledge_base']);
        });
    }
};