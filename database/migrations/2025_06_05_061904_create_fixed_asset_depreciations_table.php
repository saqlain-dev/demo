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
        Schema::create('fixed_asset_depreciations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fixed_asset_id')->nullable();
            $table->unsignedInteger('item_variant_id')->nullable();
            $table->unsignedBigInteger('register_id')->nullable();
            $table->string('fiscal_year')->nullable();
            $table->date('depreciation_start_date')->nullable();
            $table->date('depreciation_end_date')->nullable();
            $table->integer('months')->nullable();
            $table->decimal('depreciation_amount', 15, 2)->nullable();
            $table->decimal('accumulated_depreciation', 15, 2)->nullable();
            $table->decimal('net_book_value', 15, 2)->nullable();
            $table->decimal('cost', 15, 2)->nullable();
            $table->decimal('depreciation_rate', 5, 2)->default(0);
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
        Schema::dropIfExists('fixed_asset_depreciations');
    }
};
