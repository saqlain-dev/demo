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
        Schema::create('procurement_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_id')->nullable()->constrained();
            $table->foreignId('item_id')->nullable()->constrained();
            $table->foreignId('project_id')->nullable()->constrained('project_profiles');
            $table->string('budget_number')->nullable();
            $table->string('number_of_trainings')->nullable();
            $table->string('number_of_days')->nullable();
            $table->string('number_of_persons')->nullable();
            $table->decimal('estimated_amount',18);
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
        Schema::dropIfExists('documents');
    }
};
