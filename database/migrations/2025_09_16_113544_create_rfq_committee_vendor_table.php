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
        Schema::create('rfq_committee_vendor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_committee_id')
                  ->constrained('rfq_committees')
                  ->onDelete('cascade');
            $table->foreignId('vendor_id')
                  ->constrained('vendors')
                  ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfq_committee_vendor');
    }
};
