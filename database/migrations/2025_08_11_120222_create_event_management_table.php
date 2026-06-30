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
        Schema::create('event_management', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('event_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('type_values');
            $table->integer('total_attendees')->nullable();
            $table->foreignId('procurement_details_id')->nullable()->constrained('procurement_details');
            $table->boolean('venue_required')->default(false);
            $table->boolean('accommodation_required')->default(false);
            $table->integer('total_rooms')->nullable();
            $table->foreignId('room_type_id')->nullable()->constrained('type_values');
            $table->foreignId('seating_arrangement_id')->nullable()->constrained('type_values');
            $table->foreignId('board_type_id')->nullable()->constrained('type_values');
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('event_management');
    }
};
