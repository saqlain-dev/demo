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
        Schema::table('invoices', function (Blueprint $table) { 
            $table->foreignId('vm_id')->nullable()->constrained('vehicle_maintenance_forms');
            $table->foreignId('atr_id')->nullable()->constrained('air_travel_requests');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['vm_id']);
            $table->dropColumn('vm_id'); 
            $table->dropForeign(['atr_id']);
            $table->dropColumn('atr_id');
        });
    }
};
