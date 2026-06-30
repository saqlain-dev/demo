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
        Schema::create('tender_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tender_id')->constrained('tenders');
            $table->string('name')->nullable();
            $table->foreignId('nature_id')->nullable()->constrained('type_values');
            $table->string('documents_ids')->nullable();
            $table->date('opening_date')->nullable();
            $table->dateTime('closing_date')->nullable();
            $table->unsignedTinyInteger('is_comp_generated')->nullable();
            $table->unsignedTinyInteger('approval_status')->nullable();
            $table->foreignId('purchase_request_id')->nullable()->constrained('purchase_requests');
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
        Schema::dropIfExists('tender_logs');
    }
};
