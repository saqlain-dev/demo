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
        Schema::create('research_matrix_data_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_matrix_id')->nullable()->constrained('research_matrices');
            $table->integer('data_source_id')->nullable();
            $table->string('data_availability')->nullable();
            $table->string('data_availability_others')->nullable();
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
        Schema::dropIfExists('research_matrix_data_sources');
    }
};
