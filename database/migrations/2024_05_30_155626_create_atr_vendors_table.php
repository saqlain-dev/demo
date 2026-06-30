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
        Schema::create('atr_vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('atr_id')->nullable()->constrained('air_travel_requests');
            $table->foreignId('vendor_id')->nullable()->constrained('vendors');
            $table->tinyInteger('isApplied')->default(0);
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
        Schema::dropIfExists('atr_vendors');
    }
};
