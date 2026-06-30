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
        Schema::create('g_r_n_s', function (Blueprint $table) {
            $table->id();
            $table->string('grn_no')->nullable();
            $table->foreignId('po_id')->nullable()->constrained('purchase_orders');
            $table->foreignId('vendor_id')->nullable()->constrained('vendors');
            $table->dateTime('date')->nullable();
            $table->unsignedTinyInteger('status')->default(0);
            $table->text('comments')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('g_r_n_s');
    }
};
