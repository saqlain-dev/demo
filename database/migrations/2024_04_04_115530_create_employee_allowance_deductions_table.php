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
        Schema::create('employee_allowance_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained('employees');
            $table->foreignId('allowance_deduction_id')->nullable()->constrained('allowance_deductions');
            $table->string('description')->nullable();
            $table->unsignedTinyInteger('category')->nullable();
            $table->unsignedTinyInteger('calculated_by')->nullable();
            $table->unsignedFloat('value')->nullable();
            $table->unsignedTinyInteger('is_active')->default(1);
            $table->decimal('opening_balance',18,2)->nullable();
            $table->dateTime('opening_date')->nullable();
            $table->integer('employee_share_calculated_by')->nullable();
            $table->decimal('employee_share_value',18,2)->nullable();
            $table->string('modified_by')->nullable();
            $table->unsignedTinyInteger('isGlobal')->default(0);
            $table->unsignedTinyInteger('isTaxable')->default(0);
            $table->float('price_per_liter')->default(0);
            $table->float('allowed_liters')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_allowance_deductions');
    }
};
