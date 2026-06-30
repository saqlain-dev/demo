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
        Schema::table('candidate_online_tests', function (Blueprint $table) {
            $table->boolean('is_submitted')->default(false);
            $table->timestamp('test_submitted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidate_online_tests', function (Blueprint $table) {
            $table->dropColumn('is_submitted');
            $table->dropColumn('test_submitted_at');
        });
    }
};
