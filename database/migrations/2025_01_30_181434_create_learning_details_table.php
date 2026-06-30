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
        Schema::create('learning_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_log_id')->nullable()->constrained('learning_logs');
            $table->foreignId('learning_theme')->nullable()->constrained('type_values');
            $table->string('lesson_title')->nullable();
            $table->text('issue_lesson_description')->nullable();
            $table->text('recommendation_description')->nullable();
            $table->text('lesson_learnt')->nullable();
            $table->text('way_forward')->nullable();
            $table->tinyInteger('follow_up_required')->nullable();
            $table->foreignId('follow_up_required_by')->nullable()->constrained('employees');
            $table->text('follow_up_details')->nullable();
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
        Schema::dropIfExists('learning_details');
    }
};
