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
        Schema::table('finance_bills', function (Blueprint $table) {
            $table->dropColumn('amount');
            $table->dropColumn('item_detail');
            $table->dropColumn('item_coa');
            $table->dropColumn('quantity');
            $table->dropColumn('rate');
        });

        Schema::table('finance_bill_details', function (Blueprint $table) {
            $table->string('item_detail')->nullable();
            $table->string('item_coa')->nullable();
            $table->string('quantity')->nullable();
            $table->string('rate')->nullable();
            $table->renameColumn('narration', 'description');
            $table->string('total')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('finance_bills', function (Blueprint $table) {
            $table->string('amount')->nullable();
            $table->string('item_detail')->nullable();
            $table->string('item_coa')->nullable();
            $table->string('quantity')->nullable();
            $table->string('rate')->nullable();
        });

        Schema::table('finance_bill_details', function (Blueprint $table) {
            $table->dropColumn('item_detail');
            $table->dropColumn('item_coa');
            $table->dropColumn('quantity');
            $table->dropColumn('rate');
            $table->dropColumn('total');
            $table->renameColumn('description', 'narration');
        });
    }
};
