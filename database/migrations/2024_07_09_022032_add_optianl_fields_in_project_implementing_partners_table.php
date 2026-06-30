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
        Schema::table('project_implementing_partners', function (Blueprint $table) {
            $table->string('logo')->nullable();
            $table->string('focal_person')->nullable();
            $table->string('focal_person_email')->nullable();
            $table->string('focal_person_contact')->nullable();
            $table->string('donor_email')->nullable();
            $table->string('website_link')->nullable();
            $table->string('org_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_implementing_partners', function (Blueprint $table) {
            //
        });
    }
};
