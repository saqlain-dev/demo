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
        Schema::table('apply_jobs', function (Blueprint $table) {
            $table->foreignId('applier_id')->nullable()->constrained('type_values');
            $table->string('technical_proposal')->nullable();
            $table->string('financial_proposal')->nullable();
            $table->string('POCName')->nullable();
            $table->string('promisor_CNIC')->nullable();
            $table->string('NTN')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apply_jobs', function (Blueprint $table) {
            $table->dropForeign(['applier_id']);
            $table->dropColumn('applier_id');
            $table->dropColumn('technical_proposal');
            $table->dropColumn('financial_proposal');
            $table->dropColumn('POCName');
            $table->dropColumn('promisor_CNIC');
            $table->dropColumn('NTN');
        });
    }
};
