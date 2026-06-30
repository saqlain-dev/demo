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
        Schema::table('vendor_quotations', function (Blueprint $table) {
            $table->unsignedTinyInteger('is_qualified')->nullable();
            $table->text('disqualify_reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_quotations', function (Blueprint $table) {
            $table->dropColumn('is_qualified');
            $table->dropColumn('disqualify_reason');
        });
    }
};
