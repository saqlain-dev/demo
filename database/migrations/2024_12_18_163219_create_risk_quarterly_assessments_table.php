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
        Schema::create('risk_quarterly_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('risk_register_detail_id')->nullable()->constrained();
            $table->integer('quarter_id')->nullable();
            $table->text('action_taken')->nullable();
            $table->foreignId('risk_probability_id')->nullable()->constrained('type_values');
            $table->foreignId('risk_impact_id')->nullable()->constrained('type_values');
            $table->foreignId('overall_risk_id')->nullable()->constrained('type_values');
            
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
        Schema::dropIfExists('risk_quarterly_assessments');
    }
};
