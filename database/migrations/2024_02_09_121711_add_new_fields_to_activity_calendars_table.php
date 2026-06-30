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
        Schema::table('activity_calendars', function (Blueprint $table) {
            $table->string('contact_focal_person')->nullable();
            $table->foreignId('district_id')->nullable()->constrained();
            $table->string('vehicle_required')->nullable();
            $table->string('pickup_details')->nullable();
            $table->string('drop_off_details')->nullable();
            $table->text('other_details')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_calendars', function (Blueprint $table) {

        });
    }
};
