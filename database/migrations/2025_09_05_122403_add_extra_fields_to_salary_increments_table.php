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
        Schema::table('salary_increments', function (Blueprint $table) {
            $table->unsignedInteger('approval_status')->default(0);
            $table->boolean('is_effected')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_increments', function (Blueprint $table) {
            $table->dropColumn('approval_status');
            $table->dropColumn('is_effected');
        });
    }
};
