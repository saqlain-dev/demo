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
        Schema::create('audit_plan_reports', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('attachment')->nullable();
            $table->foreignId('audit_plan_id')->nullable()->constrained('audit_plans');
            $table->foreignId('prepared_by')->nullable();
            $table->text('description')->nullable();
            $table->text('status')->nullable();
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
        Schema::dropIfExists('audit_plan_reports');
    }
};
