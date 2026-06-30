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
        Schema::create('general_ledgers', function (Blueprint $table) {
            $table->id();
            $table->string('glid')->nullable();
            $table->string('voucher_no')->nullable();
            $table->foreignId('account_id')->nullable()->constrained('chart_of_accounts');
            $table->double('debit',13,2)->nullable();
            $table->double('credit',13,2)->nullable();
            $table->text('detail')->nullable();
            $table->integer('isPosted')->default(1);
            $table->date('date')->nullable();
            $table->string('financial_year')->nullable();
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
        Schema::dropIfExists('general_ledgers');
    }
};
