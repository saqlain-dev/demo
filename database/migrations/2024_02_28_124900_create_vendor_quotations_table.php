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
        Schema::create('vendor_quotations', function (Blueprint $table) {
            $table->id();
            //$table->foreignId('rfq_id')->nullable()->constrained('purchase_request_rfqs');
            $table->foreignId('vendor_id')->nullable()->constrained('vendors');
           // $table->foreignId('tender_id')->nullable()->constrained('tenders');
            $table->morphs('projectable');
            $table->unsignedTinyInteger('apply_status')->default(0);
            $table->decimal('total_quotation_amount','18',2)->nullable();
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
        Schema::dropIfExists('vendor_quotations');
    }
};
