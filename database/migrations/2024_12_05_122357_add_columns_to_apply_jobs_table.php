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
        Schema::table('apply_jobs', function (Blueprint $table) {
            $table->integer('specially_able')->nullable();
            $table->text('specialAbility')->nullable();
            $table->string('relation_with_las_employee')->nullable();
            $table->string('safety_guard_issue')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apply_jobs', function (Blueprint $table) {
            $table->dropColumn('specially_able');
            $table->dropColumn('specialAbility');
            $table->dropColumn('relation_with_las_employee');
            $table->dropColumn('safety_guard_issue');
        });
    }
};
