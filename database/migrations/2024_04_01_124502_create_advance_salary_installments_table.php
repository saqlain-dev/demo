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
        Schema::create('advance_salary_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advance_salary_id')->nullable()->constrained('advance_salaries');
            $table->unsignedInteger('installment_no')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('due_amount',16)->nullable();
            $table->unsignedTinyInteger('status')->default(0);

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
        Schema::dropIfExists('advance_salary_installments');
    }
};
