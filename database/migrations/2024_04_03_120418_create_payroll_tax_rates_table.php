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
        Schema::create('payroll_tax_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('financial_year_id')->nullable()->constrained('type_values');
            $table->dateTime('financial_year_start_date')->nullable();
            $table->dateTime('financial_year_end_date')->nullable();
            $table->string('financial_year')->nullable();
            $table->decimal('salary_from',18,2)->default(0);
            $table->decimal('salary_to',18,2)->default(0);
            $table->decimal('fixed_amount',18,2)->nullable();
            $table->float('tax_rate')->nullable();
            $table->decimal('minimum_tax_amount',18,2)->default(0);
            $table->unsignedTinyInteger('isActive')->default(1);
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
        Schema::dropIfExists('payroll_tax_rates');
    }
};
