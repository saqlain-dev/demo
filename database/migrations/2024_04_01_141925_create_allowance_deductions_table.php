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
        Schema::create('allowance_deductions', function (Blueprint $table) {
            $table->id();
            $table->string('description')->nullable();
            $table->unsignedTinyInteger('category')->nullable();
            $table->unsignedTinyInteger('calculated_by')->nullable();
            $table->foreignId('employee_type')->nullable()->constrained('type_values');
            $table->unsignedFloat('value')->nullable();
            $table->unsignedFloat('liter')->nullable();
            $table->unsignedTinyInteger('employee_calculated_by')->nullable();
            $table->unsignedFloat('employee_value')->nullable();
            $table->unsignedTinyInteger('is_active')->default(0);
            $table->unsignedTinyInteger('is_taxable')->default(0);
            $table->unsignedTinyInteger('approval_status')->default(STATUS::DRAFT);
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
        Schema::dropIfExists('allowance_deductions');
    }
};
