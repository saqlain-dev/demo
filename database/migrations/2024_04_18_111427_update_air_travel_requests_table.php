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
            $table->dropColumn('departure_from');
            $table->dropColumn('act_code');
            $table->dropColumn('donor_code');
            $table->decimal('additional_charges', 18)->nullable();
            $table->string('cancellation_reason')->nullable();
        });

        Schema::table('air_travel_requests', function (Blueprint $table) {
            $table->string('act_code')->nullable();
            $table->string('donor_code')->nullable();
            $table->string('cnic')->nullable();
            $table->decimal('additional_charges', 18)->nullable();
            $table->string('cancellation_reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('air_travel_request_details', function (Blueprint $table) {
            $table->string('departure_from')->nullable();
            $table->string('act_code')->nullable();
            $table->string('donor_code')->nullable();
            $table->dropColumn('additional_charges');
            $table->dropColumn('cancellation_reason');
        });

        Schema::table('air_travel_requests', function (Blueprint $table) {
            $table->dropColumn('act_code');
            $table->dropColumn('donor_code');
            $table->dropColumn('cnic');
            $table->dropColumn('additional_charges');
            $table->dropColumn('cancellation_reason');
        });
    }
};
