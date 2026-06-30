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
        Schema::table('approval_process_lists', function (Blueprint $table) {
            $table->dropForeign(['strategic_plan_id']);
            $table->dropColumn('strategic_plan_id');

            $table->unsignedBigInteger('request_module_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_process_lists', function (Blueprint $table) {
            //
        });
    }
};
