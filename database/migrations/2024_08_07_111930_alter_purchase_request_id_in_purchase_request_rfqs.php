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
            // Drop foreign key constraint
            $table->dropForeign(['purchase_request_id']);
        });

        Schema::table('purchase_request_rfqs', function (Blueprint $table) {
            // Modify column to allow NULL and ensure the data type matches the purchase_requests.id column
            $table->unsignedBigInteger('purchase_request_id')->nullable()->change();
        });

        Schema::table('purchase_request_rfqs', function (Blueprint $table) {
            // Re-add foreign key constraint
            $table->foreign('purchase_request_id')->references('id')->on('purchase_requests')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_request_rfqs', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['purchase_request_id']);
        });

        Schema::table('purchase_request_rfqs', function (Blueprint $table) {
            // Revert column to NOT NULL and the original data type (assuming it was integer)
            $table->integer('purchase_request_id')->nullable(false)->change();
        });

        Schema::table('purchase_request_rfqs', function (Blueprint $table) {
            // Re-add foreign key constraint
            $table->foreign('purchase_request_id')->references('id')->on('purchase_requests')->onDelete('cascade');
        });
    }
};
