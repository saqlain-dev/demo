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
        Schema::create('rm_plan_data_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rm_plan_id')->nullable()->constrained('rm_plans');
            $table->foreignId('rm_data_source')->nullable()->constrained('type_values');
            $table->foreignId('rm_data_availability')->nullable()->constrained('type_values');

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
        Schema::dropIfExists('research_matrices');
    }
};
