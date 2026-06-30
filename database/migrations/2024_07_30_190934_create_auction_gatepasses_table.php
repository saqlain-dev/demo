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
        Schema::create('auction_gate_passes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pr_rfq_id')->constrained('purchase_request_rfqs');
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->timestamp('gate_pass_date')->nullable();
            $table->text('reason')->nullable();
            $table->text('address')->nullable();
            $table->string('received_by')->nullable();

            $table->unsignedTinyInteger('approval_status')->default(STATUS::DRAFT);
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
        Schema::dropIfExists('auction_gate_passes');
    }
};
