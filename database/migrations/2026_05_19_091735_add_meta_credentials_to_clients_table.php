<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('wa_phone_number_id')->nullable()->after('wa_api_url');
            $table->string('wa_access_token')->nullable()->after('wa_phone_number_id');
            $table->string('wa_waba_id')->nullable()->after('wa_access_token');
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['wa_phone_number_id', 'wa_access_token', 'wa_waba_id']);
        });
    }
};
