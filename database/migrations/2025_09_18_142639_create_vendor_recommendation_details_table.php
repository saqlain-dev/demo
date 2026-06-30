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
        Schema::create('vendor_recommendation_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_recommendation_id')->nullable()->constrained('vendor_recommendations');
            $table->foreignId('vendor_id')->nullable()->constrained('vendors');
            $table->foreignId('item_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_recommendation_details');
    }
};
