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
        Schema::create('dispose_request_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispose_request_id')->nullable()->constrained();
            $table->foreignId('item_variant_id')->nullable()->constrained();
            $table->text('item_description')->nullable();

            $table->string('activity_code')->nullable();
            $table->string('required_quantity')->nullable();
            $table->decimal('estimated_cost',18)->nullable();
            $table->decimal('estimated_total_cost',18)->nullable();
            $table->decimal('purchase_cost',18)->nullable();


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
        Schema::dropIfExists('dispose_request_details');
    }
};
