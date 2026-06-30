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
        Schema::table('tender_details', function (Blueprint $table) {
            $table->foreignId('item_id')->nullable()->constrained();
        });

        Schema::table('tenders', function (Blueprint $table) {
            $table->foreignId('purchase_request_id')->nullable()->constrained();
            $table->decimal('sub_total', 20, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tender_details', function (Blueprint $table) {
            //
        });
    }
};
