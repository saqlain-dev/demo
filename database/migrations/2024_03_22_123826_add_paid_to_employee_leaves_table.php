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
        Schema::table('employee_leaves', function (Blueprint $table) {
            $table->unsignedTinyInteger('leave_paid_status')->nullable();
            $table->unsignedTinyInteger('leave_status')->nullable();
            $table->string('leave_file')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_leaves', function (Blueprint $table) {
            $table->dropColumn('leave_paid_status');
            $table->dropColumn('leave_status');
            $table->dropColumn('leave_file');
        });
    }
};
