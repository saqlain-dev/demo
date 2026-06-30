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
        Schema::create('consultant_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->nullable()->constrained('purchase_request_rfqs');
            $table->foreignId('tender_id')->nullable()->constrained('tenders');
            $table->foreignId('project_award_id')->nullable()->constrained('project_awardeds');
            $table->longText('contract')->nullable();
            $table->string('vendor_name')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->date('date')->nullable();
            $table->string('ntn')->nullable();
            $table->decimal('total_amount',16,2)->nullable();
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
        Schema::dropIfExists('consultant_contracts');
    }
};
