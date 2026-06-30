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
        Schema::create('employee_relatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_insurance_id')->constrained();
            $table->string('name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('age')->nullable();
            $table->foreignId('relation_id')->nullable()->constrained('type_values');
            $table->string('cnic')->nullable();

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
        Schema::dropIfExists('employee_insurances');
    }
};
