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
        Schema::create('journal_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_id')->nullable();
            $table->string('voucher_type')->nullable();
            $table->date('date')->nullable();
            $table->string('financial_year')->nullable();
            $table->double('amount',18,2)->nullable();
            $table->text('narration')->nullable();
            $table->string('instrument_id')->nullable();
            $table->integer('vendor_id')->nullable();
            $table->integer('project_id')->nullable();
            $table->integer('verified_by')->nullable();
            $table->integer('is_verified')->default(0);
            $table->integer('approval_status')->default(4);
            $table->text('reason')->nullable();
            $table->string('voucher_from_id')->nullable();
            $table->string('voucher_from')->nullable();
            $table->string('payable_to')->nullable();
            $table->string('bank_account')->nullable();
            $table->integer('sequence_no')->nullable();
            $table->integer('voucher_type_id')->nullable();
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
        Schema::dropIfExists('journal_vouchers');
    }
};
