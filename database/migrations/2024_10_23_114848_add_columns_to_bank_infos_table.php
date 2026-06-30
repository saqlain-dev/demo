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

        Schema::table('bank_infos', function (Blueprint $table) {
            $table->foreignId('las_configuration_id')->nullable()->constrained('las_configurations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_infos', function (Blueprint $table) {
            $table->dropForeign('bank_infos_las_configuration_id_foreign');
        });
        Schema::table('bank_infos', function (Blueprint $table) {
            $table->dropColumn('las_configuration_id');
        });
    }
};
