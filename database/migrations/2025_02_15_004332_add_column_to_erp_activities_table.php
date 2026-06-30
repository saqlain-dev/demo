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
        Schema::table('erp_activities', function (Blueprint $table) {
            $table->string('project')->nullable();
            $table->string('poc_name')->nullable();
            $table->string('poc_email')->nullable();
            $table->string('poc_contact')->nullable();
            $table->string('poc_designation')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_activities', function (Blueprint $table) {
            $table->dropColumn('project');
            $table->dropColumn('poc_name');
            $table->dropColumn('poc_email');
            $table->dropColumn('poc_contact');
            $table->dropColumn('poc_designation');
        });
    }
};
