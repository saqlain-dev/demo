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
        Schema::table('employee_offboardings', function (Blueprint $table) {
            $table->unsignedTinyInteger('approval_status')->default(STATUS::DRAFT);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_offboardings', function (Blueprint $table) {
            $table->dropColumn('approval_status');
        });
    }
};
