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
        Schema::create('stock_transfer_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_stock_id')->nullable()->constrained('issue_stocks');
            $table->foreignId('issue_stock_detail_id')->nullable()->constrained('issue_stock_details');
            $table->date('transfer_date')->nullable();
            $table->integer('transfer_by')->nullable();
            $table->text('remarks')->nullable();
            $table->string('attachment')->nullable();
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
        Schema::dropIfExists('stock_transfer_notes');
    }
};
