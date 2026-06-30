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
        Schema::table('purchase_request_rfqs', function (Blueprint $table) {
            $table->renameColumn('type', 'quotation_type');
            $table->unsignedTinyInteger('rf_type')->default(1);
            $table->unsignedTinyInteger('status')->default(STATUS::DRAFT);
            $table->text('comments')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_request_rfqs', function (Blueprint $table) {
            //
        });
    }
};
