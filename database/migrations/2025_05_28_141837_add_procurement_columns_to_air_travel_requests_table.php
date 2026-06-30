<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('air_travel_requests', function (Blueprint $table) {
            $table->foreignId('procurement_id')->nullable()->constrained('procurements');
            $table->foreignId('procurement_detail_id')->nullable()->constrained('procurement_details');
        });
    }

    public function down()
    {
        Schema::table('air_travel_requests', function (Blueprint $table) {
            $table->dropForeign(['procurement_id']);
            $table->dropColumn('procurement_id');
            $table->dropForeign(['procurement_detail_id']);
            $table->dropColumn('procurement_detail_id');
        });
    }

};
