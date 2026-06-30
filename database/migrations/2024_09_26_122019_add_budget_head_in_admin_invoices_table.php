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
        Schema::table('admin_invoices', function (Blueprint $table) {
            $table->integer('budget_id')->nullable();
            $table->integer('head_id')->nullable();
            $table->date('due_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_invoices', function (Blueprint $table) {
            $table->dropColumn('budget_id');
            $table->dropColumn('head_id');
            $table->dropColumn('due_date');
        });
    }
};
