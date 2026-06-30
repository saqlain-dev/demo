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
        Schema::create('payment_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no')->nullable();
            $table->integer('voucher_type')->nullable();
            $table->date('voucher_date')->nullable();
            $table->integer('payment_mode')->nullable();
            $table->string('payment_to')->nullable();
            $table->integer('location')->nullable();
            $table->integer('currency')->nullable();
            $table->integer('payment_is')->nullable();
            $table->string('cheque_no')->nullable();
            $table->string('cheque_name')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('employees');
            $table->foreignId('checked_by')->nullable()->constrained('employees');
            $table->foreignId('authorized_by')->nullable()->constrained('employees');
            $table->integer('approval_status')->nullable();
            $table->integer('status')->default(1);
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
        Schema::dropIfExists('payment_vouchers');
    }
};
