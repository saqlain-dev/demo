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
        Schema::table('procurement_details', function (Blueprint $table) {
            $table->string('activity_number')->nullable();
            $table->string('number_of_units')->nullable();
            $table->decimal('unit_price', 18, 2)->nullable();
            $table->foreignId('procurement_method')->nullable()->constrained('rfq_types');
            $table->date('contract_start_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->date('spn_publication_date')->nullable();
            $table->date('eoi_publication_date')->nullable();
            $table->foreignId('selection_method')->nullable()->constrained('type_values');
            $table->foreignId('amount_type')->nullable()->constrained('type_values');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('procurement_details', function (Blueprint $table) {
            //
        });
    }
};
