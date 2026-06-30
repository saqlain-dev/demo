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
        Schema::create('employee_change_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('EmployeeID')->nullable()->constrained('employees');
            $table->string('description')->nullable();
            $table->string('change_from')->nullable();
            $table->string('change_to')->nullable();
            $table->text('remarks')->nullable();
            $table->date('effective_date')->nullable();
            $table->integer('change_from_id')->nullable();
            $table->integer('change_to_id')->nullable();
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
        Schema::dropIfExists('employee_change_logs');
    }
};
