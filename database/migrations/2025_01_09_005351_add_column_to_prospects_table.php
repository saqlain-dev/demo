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
        Schema::table('prospects', function (Blueprint $table) {
            $table->string('email_id')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('address_line_first')->nullable();
            $table->string('address_line_sec')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->foreignId('country_id')->nullable()->constrained('countries');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->dropColumn('email_id');
            $table->dropColumn('phone_number');
            $table->dropColumn('address_line_first');
            $table->dropColumn('address_line_sec');
            $table->dropColumn('city');
            $table->dropColumn('state');
            $table->dropColumn('zip_code');
            $table->dropForeign(['country_id']);
            $table->dropColumn('country_id');
        });
    }
};
