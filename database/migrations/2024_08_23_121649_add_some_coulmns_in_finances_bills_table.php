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
            $table->renameColumn('head_id', 'item_detail');
            $table->renameColumn('class_id', 'item_coa');
            $table->renameColumn('narration', 'description');
            $table->integer('quantity')->nullable();
            $table->double('rate')->nullable();
            $table->double('total')->nullable();

        });
        Schema::table('finance_bills', function (Blueprint $table) {
            $table->string('item_detail')->change();
            $table->string('item_coa')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('finance_bills', function (Blueprint $table) {
            $table->renameColumn('item_detail', 'head_id');
            $table->renameColumn('item_coa', 'head_id');
            $table->renameColumn('description', 'narration');
            $table->dropColumn('quantity');
            $table->dropColumn('rate');
            $table->dropColumn('total');
        });
        Schema::table('finance_bills', function (Blueprint $table) {
            $table->integer('item_detail')->change();
            $table->integer('item_coa')->change();
        });
    }
};
