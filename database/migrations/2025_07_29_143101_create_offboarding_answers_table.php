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
        Schema::create('offboarding_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_offboard_id')->nullable()->constrained('employee_offboardings');
            $table->foreignId('offboarding_question_id')->nullable()->constrained();
            $table->text('answer')->nullable(); 
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
        Schema::dropIfExists('offboarding_answers');
    }
};
