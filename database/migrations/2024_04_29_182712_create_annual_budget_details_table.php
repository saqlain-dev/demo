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
        Schema::create('annual_budget_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('annual_budget_id')->nullable()->constrained('annual_budgets');
            $table->foreignId('project_id')->nullable()->constrained('project_profiles');
            $table->integer('expense_type')->nullable();
            $table->text('item_detail')->nullable();
            $table->double('budget_amount')->nullable();
            $table->text('description')->nullable();
            $table->integer('status')->default(1);
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
        Schema::dropIfExists('annual_budget_details');
    }
};
