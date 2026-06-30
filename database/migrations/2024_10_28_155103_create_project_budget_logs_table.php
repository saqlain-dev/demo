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
        Schema::create('project_budget_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_budget_id')->nullable()->constrained();
            $table->foreignId('project_id')->nullable()->constrained('project_profiles');
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->text('detail')->nullable();
            $table->decimal('total',13,2)->nullable();
            $table->integer('status')->default(1);
            
            // Original record logs
            $table->integer('approval_status')->default(STATUS::DRAFT);
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
        Schema::dropIfExists('project_budget_logs');
    }
};
