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
        Schema::create('finance_bill_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->nullable()->constrained('finance_bills');
            $table->integer('head_id')->nullable();
            $table->integer('class_id')->nullable();
            $table->double('amount',18,2)->nullable();
            $table->text('narration')->nullable();
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
        Schema::dropIfExists('finance_bill_details');
    }
};
