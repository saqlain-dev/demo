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
        Schema::table('air_travel_request_details', function (Blueprint $table) {
            if (!Schema::hasColumn('air_travel_request_details', 'seat_name')) {
                $table->string('seat_name')->nullable();
            }
        });

        Schema::table('air_travel_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('air_travel_requests', 'departure_from')) {
                $table->string('departure_from')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('air_travel_request_details', function (Blueprint $table) {
            //
        });
    }
};
