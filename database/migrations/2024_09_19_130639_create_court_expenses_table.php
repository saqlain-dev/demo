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
        Schema::create('court_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('accused_name')->nullable();
            $table->date('requested_date')->nullable();
            $table->string('case_no')->nullable();
            $table->string('fir_no')->nullable();
            $table->string('paper_requested')->nullable();
            $table->double('amount')->nullable();
            $table->integer('employee_id')->nullable();
            $table->integer('pr_id')->nullable();
            $table->string('attachment')->nullable();
            $table->integer('approval_status')->default(4);
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
        Schema::dropIfExists('court_expenses');
    }
};
