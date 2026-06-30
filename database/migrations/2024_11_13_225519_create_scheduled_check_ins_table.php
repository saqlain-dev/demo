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
        Schema::create('scheduled_check_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('performance_check_in_id')->constrained('performance_check_ins');
            $table->foreignId('employee_workplan_id')->constrained('employee_workplans');
            $table->date('check_in_date');
            $table->string('check_in_title');
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
        Schema::dropIfExists('scheduled_check_ins');
    }
};
