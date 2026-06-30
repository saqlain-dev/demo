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
        Schema::table('event_management', function (Blueprint $table) {
            $table->dropForeign(['procurement_details_id']);
            $table->dropForeign(['room_type_id']);
            $table->dropForeign(['seating_arrangement_id']);
            $table->dropForeign(['board_type_id']);

            // Drop old columns
            $table->dropColumn([
                'event_date',
                'start_time',
                'end_time',
                'total_attendees',
                'procurement_details_id',
                'venue_required',
                'accommodation_required',
                'total_rooms',
                'room_type_id',
                'seating_arrangement_id',
                'board_type_id',
                'notes'
            ]);

            // Add new columns
            $table->dateTime('start_date')->nullable()->after('title');
            $table->dateTime('end_date')->nullable()->after('start_date');
            $table->foreignId('procurement_id')->nullable()->constrained('procurements')->after('category_id');
            $table->boolean('float_vendor')->default(false)->after('procurement_id');
            $table->text('remarks')->nullable()->after('approval_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_management', function (Blueprint $table) {
             $table->dropForeign(['procurement_id']);

            // Drop new columns
            $table->dropColumn([
                'start_date',
                'end_date',
                'procurement_id',
                'float_vendor',
                'remarks'
            ]);

            // Add old columns back
            $table->date('event_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('total_attendees')->nullable();
            $table->foreignId('procurement_details_id')->nullable()->constrained('procurement_details');
            $table->boolean('venue_required')->default(false);
            $table->boolean('accommodation_required')->default(false);
            $table->integer('total_rooms')->nullable();
            $table->foreignId('room_type_id')->nullable()->constrained('type_values');
            $table->foreignId('seating_arrangement_id')->nullable()->constrained('type_values');
            $table->foreignId('board_type_id')->nullable()->constrained('type_values');
            $table->text('notes')->nullable();
        });
    }
};
