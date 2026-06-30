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
        Schema::create('claim_travel_expense_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_travel_expense_id')->nullable()->constrained('claim_travel_expenses');
            $table->string('date')->nullable();
            $table->text('description')->nullable();
            $table->double('lunch_less', 18, 2)->nullable();
            $table->double('dinner_less', 18, 2)->nullable();
            $table->double('cost', 18, 2)->nullable();
            $table->double('amount', 18, 2)->nullable();
            $table->string('attachment')->nullable();
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
        Schema::dropIfExists('claim_travel_expense_details');
    }
};
