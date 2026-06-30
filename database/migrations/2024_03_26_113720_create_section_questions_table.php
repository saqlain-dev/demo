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
        Schema::create('section_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('designation_id')->nullable()->constrained('designations');
            $table->foreignId('type_value_id')->nullable()->constrained('type_values');
            $table->text('question')->nullable();
            $table->boolean('is_active')->default(1);
            $table->boolean('is_prob_question')->default(0);

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
