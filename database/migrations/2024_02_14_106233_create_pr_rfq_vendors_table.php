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
        Schema::create('pr_rfq_vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_rfq_id')->nullable()->constrained('purchase_request_rfqs');
            $table->foreignId('vendor_id')->nullable()->constrained('vendors');
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
        Schema::dropIfExists('pr_rfq_vendors');
    }
};
