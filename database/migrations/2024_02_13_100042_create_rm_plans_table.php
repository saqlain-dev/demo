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
        Schema::create('rm_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rm_id')->nullable()->constrained('research_matrices');
            $table->tinyInteger('methodology_id')->nullable()->comment('1 for quantitative, 2 for qualitative, 3 for case file only');
            $table->foreignId('research_place_id')->nullable()->constrained('type_values');

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
