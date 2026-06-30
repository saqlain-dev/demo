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
        Schema::table('vehicle_maintenance_forms', function (Blueprint $table) {
            $table->dropForeign(['vehicle_type']);
            $table->dropColumn('vehicle_type');
            $table->dropColumn('registration_no');

            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_maintenance_forms', function (Blueprint $table) {
            //
        });
    }
};
