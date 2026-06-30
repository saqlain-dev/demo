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
        Schema::create('vehicle_request_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_request_id')->nullable()->constrained();
            $table->integer('pool_type')->nullable();
            $table->integer('vehicle_id')->nullable();
            $table->text('non_pool_detail')->nullable();
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
        Schema::dropIfExists('vehicle_request_details');
    }
};
