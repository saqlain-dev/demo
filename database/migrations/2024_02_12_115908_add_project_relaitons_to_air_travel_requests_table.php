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
        Schema::table('air_travel_requests', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->constrained('project_profiles');
            $table->foreignId('department_id')->nullable()->constrained('type_values');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('air_travel_requests', function (Blueprint $table) {
            //
        });
    }
};
