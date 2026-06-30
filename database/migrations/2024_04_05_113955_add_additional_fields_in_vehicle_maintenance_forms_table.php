<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vehicle_maintenance_forms', function (Blueprint $table) {
            $table->string('current_odo')->nullable();
        });
        Schema::table('vehicle_maintenance_details', function (Blueprint $table) {
            $table->string('parts_services')->nullable();
            $table->decimal('amount', 18, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_maintenance_forms', function (Blueprint $table) {
            $table->dropColumn('current_odo');
        });
        Schema::table('vehicle_maintenance_details', function (Blueprint $table) {
            $table->dropColumn('parts_services');
            $table->dropColumn('amount');
        });
    }
};
