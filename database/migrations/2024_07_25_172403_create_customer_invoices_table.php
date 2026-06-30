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
        Schema::create('customer_invoices', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_type')->nullable();
            $table->string('customer_coa')->nullable();
            $table->string('class')->nullable();
            $table->date('date')->nullable();
            $table->string('invoice_no')->nullable();
            $table->text('address')->nullable();
            $table->integer('terms')->nullable();
            $table->double('total_amount',18,2)->nullable();
            $table->integer('is_voucher_posted')->default(0);
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
        Schema::dropIfExists('customer_invoices');
    }
};
