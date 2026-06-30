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
        Schema::create('claim_travel_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->foreignId('employee_id')->nullable()->constrained('employees');
            $table->date('claim_date')->nullable();
            $table->date('departure_date')->nullable();
            $table->time('departure_time')->nullable();
            $table->string('departure_destination')->nullable();
            $table->date('return_date')->nullable();
            $table->time('return_time')->nullable();
            $table->string('return_destination')->nullable();
            $table->integer('approval_status')->default(4);
            $table->integer('pr_id')->nullable();
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
        Schema::dropIfExists('claim_travel_expenses');
    }
};
