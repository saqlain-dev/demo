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
        Schema::table('position_wise_allow_deducts', function (Blueprint $table) {
            if (Schema::hasColumn('position_wise_allow_deducts', 'employee_id')) {
                $table->dropForeign(['employee_id']);
            }

        });
        Schema::table('position_wise_allow_deducts', function (Blueprint $table) {
            if (Schema::hasColumn('position_wise_allow_deducts', 'employee_id')) {
                $table->dropColumn('employee_id');
            }
            if (Schema::hasColumn('position_wise_allow_deducts', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('position_wise_allow_deducts', 'category')) {
                $table->dropColumn('category');
            }
            if (Schema::hasColumn('position_wise_allow_deducts', 'calculated_by')) {
                $table->dropColumn('calculated_by');
            }
            if (Schema::hasColumn('position_wise_allow_deducts', 'value')) {
                $table->dropColumn('value');
            }

            if (Schema::hasColumn('position_wise_allow_deducts', 'is_active')) {
                $table->dropColumn('is_active');
            }
            if (Schema::hasColumn('position_wise_allow_deducts', 'opening_balance')) {
                $table->dropColumn('opening_balance');
            }
            if (Schema::hasColumn('position_wise_allow_deducts', 'opening_date')) {
                $table->dropColumn('opening_date');
            }
            if (Schema::hasColumn('position_wise_allow_deducts', 'employee_share_calculated_by')) {
                $table->dropColumn('employee_share_calculated_by');
            }
            if (Schema::hasColumn('position_wise_allow_deducts', 'employee_share_value')) {
                $table->dropColumn('employee_share_value');
            }
            if (Schema::hasColumn('position_wise_allow_deducts', 'modified_by')) {
                $table->dropColumn('modified_by');
            }
            if (Schema::hasColumn('position_wise_allow_deducts', 'isGlobal')) {
                $table->dropColumn('isGlobal');
            }
            if (Schema::hasColumn('position_wise_allow_deducts', 'isTaxable')) {
                $table->dropColumn('isTaxable');
            }
            if (Schema::hasColumn('position_wise_allow_deducts', 'price_per_liter')) {
                $table->dropColumn('price_per_liter');
            }
            if (Schema::hasColumn('position_wise_allow_deducts', 'allowed_liters')) {
                $table->dropColumn('allowed_liters');
            }

            if (!Schema::hasColumn('position_wise_allow_deducts', 'position_id')) {
                $table->foreignId('position_id')->nullable()->constrained('designations');
            }

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('position_wise_allow_deducts', function (Blueprint $table) {
            //
        });
    }
};
