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
        Schema::create('journal_voucher_details', function (Blueprint $table) {
            $table->id();
            $table->string('journal_voucher_id')->nullable();
            $table->string('voucher_type')->nullable();
            $table->date('date')->nullable();
            $table->string('financial_year')->nullable();
            $table->string('nominal_id')->nullable();
            $table->string('nominal_class')->nullable();
            $table->string('nominal_class_id')->nullable();
            $table->double('credit', 18,2)->nullable();
            $table->double('debit',18,2)->nullable();
            $table->text('detail')->nullable();
            $table->string('voucher_type_id')->nullable();
            $table->integer('vendor_id')->nullable();
            $table->integer('project_id')->nullable();
            $table->integer('is_posted')->default(0);
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
        Schema::dropIfExists('journal_voucher_details');
    }
};
