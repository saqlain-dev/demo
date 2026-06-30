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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->date('last_acknowledgement_date')->nullable()->after('updated_at');
        });

        Schema::table('work_orders', function (Blueprint $table) {
            $table->date('last_acknowledgement_date')->nullable()->after('updated_at');
        });

        Schema::table('consultant_contracts', function (Blueprint $table) {
            $table->date('last_acknowledgement_date')->nullable()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('last_acknowledgement_date');
        });

        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn('last_acknowledgement_date');
        });

        Schema::table('consultant_contracts', function (Blueprint $table) {
            $table->dropColumn('last_acknowledgement_date');
        });
    }
};
