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
        Schema::table('donar_profiles', function (Blueprint $table) {
            $table->string('donor_official_email')->nullable();
            $table->string('focal_person')->nullable();
            $table->string('focal_person_email')->nullable();
            $table->string('focal_person_contact')->nullable();
            $table->string('website_link')->nullable();
            $table->integer('org_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donar_profiles', function (Blueprint $table) {
            $table->dropColumn('donor_official_email');
            $table->dropColumn('focal_person');
            $table->dropColumn('focal_person_email');
            $table->dropColumn('focal_person_contact');
            $table->dropColumn('website_link');
            $table->dropColumn('org_type');
        });
    }
};
