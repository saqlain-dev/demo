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
        Schema::create('performance_factors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('performance_planning_id')->nullable()->constrained();
            $table->unsignedTinyInteger('performance_factor_type')->nullable();
            $table->foreignId('performance_factor_value')->nullable()->constrained('type_values');
            $table->decimal('total_points',5)->default(10);
            $table->decimal('awarded_points',5)->nullable();
            $table->text('comments')->nullable();
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
        Schema::dropIfExists('performance_factors');
    }
};
