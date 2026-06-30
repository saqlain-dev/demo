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
        Schema::create('advance_salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained();
            $table->foreignId('loan_category_id')->nullable()->constrained('type_values');
            $table->decimal('advance_salary',16)->nullable();
            $table->unsignedInteger('number_of_installments')->nullable();
            $table->date('first_installment_date')->nullable();
            $table->date('insurance_date')->nullable();
            $table->text('remarks')->nullable();

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
        Schema::dropIfExists('advance_salaries');
    }
};
