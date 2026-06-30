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
        Schema::table('reimbursements', function (Blueprint $table) {
            $table->integer('pr_id')->nullable();
        });

        Schema::table('reimbursement_expenses', function (Blueprint $table) {
            $table->string('attachment')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reimbursements', function (Blueprint $table) {
            $table->dropColumn('pr_id');
        });
        Schema::table('reimbursement_expenses', function (Blueprint $table) {
            $table->dropColumn('attachment');
        });
    }
};
