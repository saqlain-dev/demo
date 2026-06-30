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
        Schema::table('apr_follow_ups', function (Blueprint $table) {
            $table->text('task')->nullable();
            $table->date('checkin_date')->nullable();
            $table->foreignId('priority')->nullable()->constrained('type_values');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apr_follow_ups', function (Blueprint $table) {
            $table->dropColumn('task');
            $table->dropColumn('checkin_date');
            $table->dropForeign(['priority']);
            $table->dropColumn('priority');
        });
    }
};
