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
        Schema::table('yearly_leave_entitlements', function (Blueprint $table) {
            $table->unsignedBigInteger('yearly_entitlement_leave')->change();
        });
        Schema::table('leave_balances', function (Blueprint $table) {
            $table->unsignedDecimal('Balance',8,2)->nullable()->change();
            $table->unsignedDecimal('Availed',8,2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('yearly_leave_entitlements', function (Blueprint $table) {
            $table->unsignedSmallInteger('yearly_entitlement_leave')->change();
        });

        Schema::table('leave_balances', function (Blueprint $table) {
            $table->decimal('Balance',5,2)->nullable()->change();
            $table->decimal('Availed',5,2)->nullable()->change();
        });
    }
};
