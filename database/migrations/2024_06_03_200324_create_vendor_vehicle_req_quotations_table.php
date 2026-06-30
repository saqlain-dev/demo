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
        Schema::create('vendor_vehicle_req_quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_req_id')->nullable()->constrained('vehicle_requests');
            $table->foreignId('vendor_id')->nullable()->constrained('vendors');
            $table->string('per_day_rate')->nullable();
            $table->string('per_km')->nullable();
            $table->text('remarks')->nullable();
            $table->tinyInteger('quotation_status')->default(0);
            $table->text('quotation_accepted_remarks')->nullable();
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
        Schema::dropIfExists('vendor_vehicle_req_quotations');
    }
};
