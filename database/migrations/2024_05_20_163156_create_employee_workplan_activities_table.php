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
        Schema::create('employee_workplan_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_workplan_id')->nullable()->constrained('employee_workplans');
            $table->text('activity')->nullable();
            $table->text('sub_activity')->nullable();
            $table->text('description')->nullable();
            $table->string('area')->nullable();
            $table->string('task')->nullable();
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
        Schema::dropIfExists('employee_workplan_activities');
    }
};
