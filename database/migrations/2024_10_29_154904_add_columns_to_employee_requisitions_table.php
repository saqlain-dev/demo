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
        Schema::table('employee_requisitions', function (Blueprint $table) {
            $table->integer('recruitment_plan_id')->nullable();
            $table->integer('recruitment_plan_detail_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_requisitions', function (Blueprint $table) {
            $table->dropColumn('recruitment_plan_id');
            $table->dropColumn('recruitment_plan_detail_id');
        });
    }
};
