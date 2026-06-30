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
        Schema::table('work_completions', function (Blueprint $table) {
            $table->foreignId('branch_office_id')->nullable()->constrained('branch_offices');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_completions', function (Blueprint $table) {
            $table->dropForeign(['branch_office_id']);
            $table->dropColumn('branch_office_id');
        });
    }
};
