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
        Schema::table('leave_add_deducts', function (Blueprint $table) {
            $table->foreignId('FYID')->nullable()->constrained('financial_years');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_add_deducts', function (Blueprint $table) {
            $table->dropForeign(['FYID']);
            $table->dropColumn('FYID');
        });
    }
};
