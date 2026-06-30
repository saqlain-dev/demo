<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProvinceIdAndCoaIdToTaxManagementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tax_management', function (Blueprint $table) {
            $table->unsignedBigInteger('province_id')->nullable()->after('id');
            $table->unsignedBigInteger('coa_id')->nullable()->after('province_id'); 
            $table->foreign('province_id')->references('id')->on('provinces')->onDelete('cascade');
            $table->foreign('coa_id')->references('id')->on('chart_of_accounts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tax_management', function (Blueprint $table) {
            $table->dropColumn('province_id');
            $table->dropColumn('coa_id'); 
            $table->dropForeign(['province_id']);
            $table->dropForeign(['coa_id']);
        });
    }
}

