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
        Schema::create('acknowledgement_histories', function (Blueprint $table) {
            $table->id();
            $table->string('model_type'); // e.g., PurchaseOrder, WorkOrder, ConsultantContract
            $table->unsignedBigInteger('model_id');
            $table->date('old_acknowledgement_date')->nullable();
            $table->date('new_acknowledgement_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable(); // user who updated it
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acknowledgement_histories');
    }
};
