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
        Schema::create('retirement_benefits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained('employees');
            $table->date('joining_date')->nullable();
            $table->date('resignation_date')->nullable();
            $table->decimal('salary',19,3)->nullable();
            $table->string('years',100)->nullable();
            $table->string('months')->nullable();
            $table->string('years_of_calc_gratuity',100)->nullable();
            $table->decimal('gratuity_amount',19,3)->nullable();
            $table->integer('status')->default(1);
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
        Schema::dropIfExists('retirement_benefits');
    }
};
