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
        Schema::create('tax_filing_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->nullable();
            $table->tinyInteger('filing_status')->nullable();
            $table->tinyInteger('tax_type')->nullable();
            $table->integer('voucher_id')->nullable();
            $table->integer('employee_id')->nullable();
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
        Schema::dropIfExists('tax_filing_statuses');
    }
};
