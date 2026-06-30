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
            $table->decimal('revised_amount', 18)->nullable();
            $table->string('cnic')->nullable();
            $table->dropColumn('arrival_at');
            $table->string('seat_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('air_travel_request_details', function (Blueprint $table) {
            $table->dropColumn('revised_amount');
            $table->dropColumn('cnic');
            $table->dropColumn('seat_name');
        });
    }
};
