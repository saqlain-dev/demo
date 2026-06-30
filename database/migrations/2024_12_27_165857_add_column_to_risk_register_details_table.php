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
        Schema::table('risk_register_details', function (Blueprint $table) {
            $table->foreignId('risk_owner_id')->nullable()->constrained('employees');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('risk_register_details', function (Blueprint $table) {
            $table->dropForeign(['risk_owner_id']);
            $table->dropColumn('risk_owner_id');
        });
    }
};
