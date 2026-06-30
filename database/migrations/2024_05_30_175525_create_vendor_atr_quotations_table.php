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
        Schema::create('vendor_atr_quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('atr_id')->nullable()->constrained('air_travel_requests');
            $table->foreignId('vendor_id')->nullable()->constrained('vendors');
            $table->dateTime('date_time')->nullable();
            $table->foreignId('airline')->nullable()->constrained('type_values');
            $table->float('ticket_fare')->nullable();
            $table->foreignId('airline_category')->nullable()->constrained('type_values');
            $table->text('remarks')->nullable();
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
        Schema::dropIfExists('vendor_atr_quotations');
    }
};
