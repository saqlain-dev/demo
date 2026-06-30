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
        Schema::create('progress_workplans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('project_profiles');
            $table->string('project-workplan',100)->nullable();
            $table->string('goal_number')->nullable();
            $table->string('goal_statement')->nullable();
            $table->string('indicator_number')->nullable();
            $table->string('indicator_statement')->nullable();
            $table->string('baseline')->nullable();
            $table->string('lop_target')->nullable();
            $table->string('quarterly_target')->nullable();
            $table->string('progress')->nullable();
            $table->string('activities')->nullable();
            $table->string('movs')->nullable();
            $table->string('status')->nullable();
            $table->string('timeline')->nullable();

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
        Schema::dropIfExists('progress_workplans');
    }
};
