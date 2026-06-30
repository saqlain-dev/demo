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
        Schema::create('audit_trail_logs', function (Blueprint $table) {
            $table->id();
            $table->string('prf_no')->nullable();
            $table->string('project')->nullable();
            $table->date('prf_date')->nullable();
            $table->date('po_date')->nullable();
            $table->date('admin_receiving_date')->nullable();
            $table->date('invoice_received_date')->nullable();
            $table->date('submission_to_audit_date')->nullable();
            $table->string('invoice_no')->nullable();
            $table->string('vendor_name')->nullable();
            $table->string('location')->nullable();
            $table->string('sub_domain')->nullable();
            $table->double('amount',18,2)->nullable();
            $table->text('observation')->nullable();
            $table->date('resubmission_date')->nullable();
            $table->date('submission_to_finance')->nullable();
            $table->date('clearance_from_finance')->nullable();
            $table->date('voucher_date')->nullable();
            $table->string('voucher_no')->nullable();
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
        Schema::dropIfExists('audit_trail_logs');
    }
};
