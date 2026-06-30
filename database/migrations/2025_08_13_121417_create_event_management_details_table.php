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
        Schema::create('event_management_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_management_id')->nullable()->constrained('event_management');
            $table->foreignId('procurement_details_id')->nullable()->constrained('procurement_details');
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->integer('total_attendees')->nullable();
            $table->integer('total_rooms')->nullable();
            $table->foreignId('room_type_id')->nullable()->constrained('type_values');
            $table->foreignId('seating_arrangement_id')->nullable()->constrained('type_values');
            $table->foreignId('board_type_id')->nullable()->constrained('type_values');
            $table->text('remarks')->nullable();
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
        Schema::dropIfExists('event_management_details');
    }
};
