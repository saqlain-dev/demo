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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_series')->nullable();
            $table->foreignId('quotation_status')->nullable()->constrained('type_values');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers');
            $table->foreignId('lead_id')->nullable()->constrained('leads');
            $table->date('date')->nullable();
            $table->date('valid_till_date')->nullable();
            $table->string('company')->nullable();
            $table->string('quotation_number')->nullable();
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
        Schema::dropIfExists('quotations');
    }
};
