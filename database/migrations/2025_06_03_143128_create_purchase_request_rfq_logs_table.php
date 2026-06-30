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
        Schema::create('purchase_request_rfq_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_rfq_id')->constrained('purchase_request_rfqs');
            $table->date('expiry_date')->nullable();
            $table->string('action');
            $table->json('changes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_request_rfq_logs');
    }
};
