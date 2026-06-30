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
        Schema::create('court_advocate_expenses', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id')->nullable();
            $table->double('amount')->nullable()->default(0); 
            $table->date('requested_date')->nullable();
            $table->integer('pr_id')->nullable(); 
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
        Schema::dropIfExists('court_advocate_expenses');
    }
};
