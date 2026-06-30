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
        Schema::create('event_mangement_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices');
            $table->foreignId('event_management_id')->nullable()->constrained('event_management');
            $table->foreignId('event_management_details_id')->nullable()->constrained('event_management_details');
            $table->foreignId('vendor_id')->nullable()->constrained('vendors');
            $table->string('venue_name')->nullable();
            $table->integer('total_seats')->nullable();
            $table->decimal('fair_per_day', 10, 2)->nullable();
            $table->integer('days')->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->integer('total_rooms')->nullable();
            $table->text('remarks')->nullable();
            $table->string('address')->nullable();
            $table->foreignId('quotation_id')->nullable()->constrained('vendor_event_managment_quotations');
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
        Schema::dropIfExists('event_mangement_invoices');
    }
};
