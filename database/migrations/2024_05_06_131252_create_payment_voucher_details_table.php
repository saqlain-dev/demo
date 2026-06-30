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
        Schema::create('payment_voucher_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_voucher_id')->nullable()->constrained('payment_vouchers');
            $table->foreignId('account_id')->nullable()->constrained('chart_of_accounts');
            $table->text('detail')->nullable();
            $table->string('act_code')->nullable();
            $table->string('project_code')->nullable();
            $table->double('amount',13,2)->nullable();
            $table->date('date')->nullable();
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
        Schema::dropIfExists('payment_voucher_details');
    }
};
