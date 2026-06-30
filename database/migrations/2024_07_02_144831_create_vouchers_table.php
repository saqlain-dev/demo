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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('VoucherID')->nullable();
            $table->string('VoucherType')->nullable();
            $table->date('Date')->nullable();
            $table->string('FinancialYear')->nullable();
            $table->double('Amount',18,2)->nullable();
            $table->text('narration')->nullable();
            $table->string('Instrument_Id')->nullable();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors');
            $table->foreignId('project_id')->nullable()->constrained('project_profiles');
            $table->string('CreatedBy')->nullable();
            $table->foreignId('VerifiedBy')->nullable()->constrained('users');
            $table->tinyInteger('IsVerified')->default(0);
            $table->foreignId('PostedBy')->nullable()->constrained('users');
            $table->tinyInteger('IsPosted')->default(0);
            $table->unsignedInteger('SequenceNumber ')->default(0);
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
        Schema::dropIfExists('vouchers');
    }
};
