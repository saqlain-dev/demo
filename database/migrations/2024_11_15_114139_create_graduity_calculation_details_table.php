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
        Schema::create('gratuity_calculation_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gratuity_calculation_id')->nullable()->constrained('gratuity_calculations');
            $table->foreignId('employee_id')->nullable()->constrained('employees');
            $table->double('gratuity_amount')->nullable();
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
        Schema::dropIfExists('gratuity_calculation_details');
    }
};
