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
        Schema::table('approval_processes', function (Blueprint $table) {
            $table->tinyInteger('isFinancialApproval')->default(0);
            $table->double('financialAmount',18,2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_processes', function (Blueprint $table) {
            $table->dropColumn('isFinancialApproval');
            $table->dropColumn('financialAmount');
        });
    }
};
