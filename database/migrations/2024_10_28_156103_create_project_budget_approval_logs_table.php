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
        Schema::create('project_budget_approval_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_budget_log_id')->nullable()->constrained('project_budget_logs');
            $table->foreignId('approval_process_id')->nullable()->constrained('approval_process_names');
            $table->foreignId('designation_id')->nullable()->constrained('designations');
            $table->unsignedTinyInteger('approval_status')->nullable()->default(2);
            $table->unsignedTinyInteger('process_order')->nullable();
            $table->text('comments')->nullable();
            $table->tinyInteger('approval_request_status')->default(1);

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
        Schema::dropIfExists('project_budget_approval_logs');
    }
};
