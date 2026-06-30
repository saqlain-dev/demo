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
        Schema::create('finance_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors');
            $table->date('date')->nullable();
            $table->text('address')->nullable();
            $table->double('amount',18,2)->nullable();
            $table->string('terms')->nullable();
            $table->date('bill_due_date')->nullable();
            $table->integer('head_id')->nullable();
            $table->integer('class_id')->nullable();
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
        Schema::dropIfExists('finance_bills');
    }
};
