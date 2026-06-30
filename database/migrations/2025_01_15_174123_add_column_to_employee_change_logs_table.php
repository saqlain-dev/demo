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
        Schema::table('employee_change_logs', function (Blueprint $table) {
            $table->foreignId('status_change_req_id')->nullable()->constrained('employee_status_changes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_change_logs', function (Blueprint $table) {
            $table->dropForeign(['status_change_req_id']);
            $table->dropColumn('status_change_req_id');
        });
    }
};
