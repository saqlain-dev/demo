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
        Schema::create('las_invoice_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('las_invoice_id')->nullable()->constrained('las_invoices');
            $table->string('item')->nullable();
            $table->text('description')->nullable();
            $table->integer('qty')->nullable();
            $table->integer('unit')->nullable();
            $table->double('amount')->nullable();
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
        Schema::dropIfExists('las_invoice_details');
    }
};
