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
        if (!Schema::hasColumn('position_wise_allow_deducts', 'allowance_deduction_id')) {
            Schema::table('position_wise_allow_deducts', function (Blueprint $table) {
                $table->foreignId('allowance_deduction_id')->nullable()->constrained('allowance_deductions');
            });
        }
        if (!Schema::hasColumn('position_wise_allow_deducts', 'position_id')) {
            Schema::table('position_wise_allow_deducts', function (Blueprint $table) {
                $table->foreignId('position_id')->nullable()->constrained('designations');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

            Schema::table('position_wise_allow_deducts', function (Blueprint $table) {

            });


    }
};
