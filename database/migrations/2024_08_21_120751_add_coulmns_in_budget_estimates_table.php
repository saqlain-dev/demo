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
        Schema::table('budget_estimates', function (Blueprint $table) {
            $table->dropColumn(['customer_id','customer_name','customer_type','customer_coa','class']);
        });
        Schema::table('budget_estimates', function (Blueprint $table){
            $table->string('name')->nullable();
            $table->integer('type')->nullable();
            $table->unsignedBigInteger('refferenceable_id')->nullable();
            $table->string('refferenceable_type')->nullable();
            $table->integer('total_amount')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_estimates', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_type')->nullable();
            $table->string('customer_coa')->nullable();
            $table->string('class')->nullable();
        });

        Schema::table('budget_estimates', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('type');
            $table->dropColumn(['refferenceable_id', 'refferenceable_type']); // Drops 'refferenceable_id' and 'refferenceable_type' columns
            $table->dropColumn('total_amount');
        });
    }
};
