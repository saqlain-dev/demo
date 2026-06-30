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
        Schema::table('companies', function (Blueprint $table) {
            $table->string('poc_name')->nullable();
            $table->string('designation')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile_no')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('phone_ext')->nullable();
        });
       /* Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['currency_id']);

        });
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('letter_head');
            $table->dropColumn('abbr');
            $table->dropColumn('date_of_establishment');
            $table->dropColumn('currency_id');
        });*/
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('email');
            $table->dropColumn('mobile_no');
            $table->dropColumn('phone');
            $table->dropColumn('website');
            $table->dropColumn('whatsapp');
            $table->dropColumn('phone_ext');
        });
    }
};
