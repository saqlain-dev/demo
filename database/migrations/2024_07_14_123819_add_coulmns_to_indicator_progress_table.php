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
        Schema::table('indicator_progress', function (Blueprint $table) {
            $table->string('progress')->nullable();
            $table->string('budget_spent')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('indicator_progress', function (Blueprint $table) {
            $table->dropColumn('progress');
            $table->dropColumn('budget_spent');
        });
    }
};
