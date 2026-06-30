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
        Schema::create('leave_add_deducts', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('type')->nullable();
            $table->foreignId('leave_type_id')->nullable()->constrained('type_values');
            $table->foreignId('EmployeeID')->nullable()->constrained('employees');
            $table->unsignedTinyInteger('NoOfDays')->nullable();
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
        Schema::dropIfExists('leave_add_deducts');
    }
};
