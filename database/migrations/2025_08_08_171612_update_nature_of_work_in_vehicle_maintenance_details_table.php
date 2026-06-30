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
         // Step 1: Drop the old string column
        Schema::table('vehicle_maintenance_details', function (Blueprint $table) {
            $table->dropColumn('nature_of_work');
        });

        // Step 2: Add the foreignId column in a separate call
        Schema::table('vehicle_maintenance_details', function (Blueprint $table) {
            $table->foreignId('nature_of_work')->nullable()->constrained('items');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_maintenance_details', function (Blueprint $table) {
            $table->dropForeign(['nature_of_work']);
            $table->dropColumn('nature_of_work');
        });

        // Step 2: Recreate the original string column
        Schema::table('vehicle_maintenance_details', function (Blueprint $table) {
            $table->string('nature_of_work')->nullable();
        });
    }
};
