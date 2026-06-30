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
        Schema::table('appraisal_salary_setups', function (Blueprint $table) {
            $table->decimal('old_monthly_salary',18,2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appraisal_salary_setups', function (Blueprint $table) {
            $table->dropColumn('old_monthly_salary');
        });
    }
};
