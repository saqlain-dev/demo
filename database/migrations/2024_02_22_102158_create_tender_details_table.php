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
        Schema::create('tender_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tender_id')->nullable()->constrained('tenders');
            $table->foreignId('purchase_request_id')->constrained('purchase_requests');
            $table->foreignId('purchase_request_detail_id')->constrained('purchase_request_details');
            $table->text('description')->nullable();
            $table->string('unit_of_measurement')->nullable();
            $table->integer('required_quantity')->nullable();
            $table->decimal('unit_price',18,2)->nullable();
            $table->decimal('amount',18,2)->nullable();
            $table->string('version')->nullable();
            $table->string('pages')->nullable();

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
        Schema::dropIfExists('tenders');
    }
};
