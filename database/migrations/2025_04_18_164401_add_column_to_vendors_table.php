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
        Schema::table('vendors', function (Blueprint $table) {
            $table->integer('district_id')->nullable();
            $table->integer('stock_position')->nullable();
            $table->integer('company_position')->nullable();
            $table->integer('any_suspicious_activity')->nullable();
            $table->integer('business_card')->nullable();
            $table->integer('undertaking_by_vendor')->nullable();
            $table->string('visiting_team_member_f')->nullable();
            $table->string('visiting_team_member_s')->nullable();
            $table->string('email_address_f')->nullable();
            $table->integer('email_address_s')->nullable();
            $table->date('visit_date')->nullable();
            $table->string('doc_attachment')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('district_id');
            $table->dropColumn('stock_position');
            $table->dropColumn('company_position');
            $table->dropColumn('any_suspicious_activity');
            $table->dropColumn('business_card');
            $table->dropColumn('undertaking_by_vendor');
            $table->dropColumn('visiting_team_member_f');
            $table->dropColumn('visiting_team_member_s');
            $table->dropColumn('email_address_f');
            $table->dropColumn('email_address_s');
            $table->dropColumn('visit_date');
            $table->dropColumn('doc_attachment');
        });
    }
};
