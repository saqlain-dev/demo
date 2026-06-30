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
        Schema::create('leave_balance_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('LeaveTypeID')->nullable();
            $table->decimal('LeaveBalance',5,2)->nullable();
            $table->foreignId('FYID')->nullable()->constrained('financial_years');
            $table->dateTime('EntryDate')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_balance_details');
    }
};
