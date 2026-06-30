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
            $table->boolean('is_external_visitor')->default(0);
            $table->foreignId('external_visitor_id')->nullable()->constrained('type_values');
            $table->foreignId('airline_category_id')->nullable()->constrained('type_values');
            $table->string('arrival_at')->nullable();
            $table->string('departure_from')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('air_travel_requests', function (Blueprint $table) {
            $table->dropColumn('is_external_visitor');
            $table->dropForeign(['external_visitor_id']);
            $table->dropColumn('external_visitor_id');

            $table->dropForeign(['airline_category_id']);
            $table->dropColumn('airline_category_id');

            $table->dropColumn('arrival_at');
            $table->dropColumn('departure_from');
        });
    }
};
