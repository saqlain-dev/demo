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
        Schema::create('purchase_request_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->nullable()->constrained('purchase_requests');
            $table->string('activity_code',50)->nullable();
            $table->string('required_unit')->nullable();
            $table->unsignedInteger('required_quantity')->nullable();
            $table->decimal('estimated_cost',18)->nullable();
            $table->decimal('estimated_total_cost',18)->nullable();
            $table->text('purpose')->nullable();


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
        Schema::dropIfExists('purchase_request_details');
    }
};
