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
        Schema::create('purchase_request_rfqs', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('type')->nullable();
            $table->foreignId('purchase_request_id')->constrained('purchase_requests');
            $table->longText('details')->nullable();
            $table->longText('terms_conditions')->nullable();
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
        Schema::dropIfExists('purchase_request_rfqs');
    }
};
