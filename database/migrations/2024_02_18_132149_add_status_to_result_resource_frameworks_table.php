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
        Schema::table('result_resource_frameworks', function (Blueprint $table) {
            $table->unsignedTinyInteger('status')->default(STATUS::DRAFT);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('result_resource_frameworks', function (Blueprint $table) {
            //
        });
    }
};
