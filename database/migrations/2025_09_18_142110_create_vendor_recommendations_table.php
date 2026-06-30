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
        Schema::create('vendor_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tender_id')->nullable()->constrained('tenders');
            $table->foreignId('rfq_id')->nullable()->constrained('purchase_request_rfqs');
            $table->string('comments')->nullable();
            $table->integer('type')->default(0);
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
        Schema::dropIfExists('vendor_recommendations');
    }
};
