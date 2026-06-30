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
            $table->text('issue')->nullable();
            $table->integer('responsible_person')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apr_follow_ups', function (Blueprint $table) {
            $table->dropColumn('issue');
            $table->dropColumn('responsible_person');
        });
    }
};
