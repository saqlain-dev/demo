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
        Schema::create('vehicle_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles');
            $table->string('movement_from')->nullable();
            $table->string('movement_to')->nullable();
            $table->integer('total_km')->nullable();
            $table->string('commuters')->nullable();
            $table->dateTime('expected_date_from')->nullable();
            $table->dateTime('expected_date_to')->nullable();
            $table->text('purpose')->nullable();
            $table->tinyInteger('status')->default(STATUS::PENDING);
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
        Schema::dropIfExists('vehicle_requests');
    }
};
