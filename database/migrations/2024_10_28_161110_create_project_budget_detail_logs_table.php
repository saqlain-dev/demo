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
        Schema::create('project_budget_detail_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_budget_log_id')->nullable()->constrained('project_budget_logs');
            $table->integer('category_id')->nullable();
            $table->integer('sub_category_id')->nullable();
            $table->string('unit')->nullable();
            $table->string('number')->nullable();
            $table->double('amount', 13, 2)->nullable();
            $table->string('rate')->nullable();
            $table->double('requested_funds',13,2)->nullable();
            $table->double('cost_shared_applicants',13,2)->nullable();
            $table->double('program_total',13,2)->nullable();
            $table->double('sub_total',13,2)->nullable();
            $table->double('grand_total',13,2)->nullable();
            $table->integer('status')->default(1);
            $table->integer('budget_for')->nullable();
            $table->integer('head_id')->nullable();
            $table->foreignId('activity_id')->nullable()->constrained('activities');
            
            // Original record logs
            $table->foreignId('log_created_by')->nullable()->constrained('users');
            $table->foreignId('log_updated_by')->nullable()->constrained('users');
            $table->datetime('log_created_at')->nullable();
            $table->datetime('log_updated_at')->nullable();

            // Current record logs
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
        Schema::dropIfExists('project_budget_detail_logs');
    }
};
