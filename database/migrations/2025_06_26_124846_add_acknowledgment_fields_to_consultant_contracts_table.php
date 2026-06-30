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
        Schema::table('consultant_contracts', function (Blueprint $table) {
            $table->boolean('acknowledgment')->default(0)->after('total_amount');
            $table->dateTime('acknowledgment_date')->nullable()->after('acknowledgment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultant_contracts', function (Blueprint $table) {
            $table->dropColumn(['acknowledgment', 'acknowledgment_date']);
        });
    }
};
