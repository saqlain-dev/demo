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
        Schema::create('strategic_plan_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('strategic_plan_pillar_id')->constrained();
            $table->string('name')->nullable();
            $table->string('baseline')->nullable();
            $table->string('source')->nullable();
            $table->unsignedTinyInteger('status')->comment('1 for Planned 2 for Actual');
            $table->unsignedFloat('target')->nullable();
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
        Schema::dropIfExists('strategic_plan_indicators');
    }
};
