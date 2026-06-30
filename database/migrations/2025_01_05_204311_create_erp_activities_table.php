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
        Schema::create('erp_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activityable_id');
            $table->string('activityable_type');
            $table->foreignId('performed_by')->nullable()->constrained('employees');
            $table->foreignId('activity_state')->nullable()->constrained('type_values');
            $table->foreignId('activity_type')->nullable()->constrained('type_values');
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->text('description')->nullable();
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
        Schema::dropIfExists('erp_activities');
    }
};
