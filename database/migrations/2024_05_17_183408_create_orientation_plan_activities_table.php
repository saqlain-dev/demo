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
        Schema::create('orientation_plan_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orientation_plan_id')->nullable()->constrained('orientation_plans');
            $table->text('main_activity')->nullable();
            $table->text('sub_activity')->nullable();
            $table->string('venue')->nullable();
            $table->time('time')->nullable();
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
        Schema::dropIfExists('orientation_plan_activities');
    }
};
