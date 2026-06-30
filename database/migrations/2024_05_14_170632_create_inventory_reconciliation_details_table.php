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
        Schema::create('inventory_reconciliation_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_reconciliation_id')->nullable()->constrained('inventory_reconciliations');
            $table->foreignId('inventory_id')->nullable()->constrained('inventories');
            $table->integer('actual_qty')->nullable();
            $table->integer('difference')->nullable();
            $table->text('remarks')->nullable();
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
        Schema::dropIfExists('inventory_reconciliation_details');
    }
};
