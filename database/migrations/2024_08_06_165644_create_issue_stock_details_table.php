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
        Schema::create('issue_stock_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_stock_id')->nullable()->constrained('issue_stocks');
            $table->integer('item_id')->nullable();
            $table->integer('qty')->nullable();
            $table->text('remarks')->nullable();
            $table->string('policy_document')->nullable();
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
        Schema::dropIfExists('issue_stock_details');
    }
};
