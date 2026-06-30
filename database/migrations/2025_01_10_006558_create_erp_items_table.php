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
        Schema::create('erp_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_name')->nullable();
            $table->string('item_code')->nullable();
            $table->string('item_type')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('erp_item_categories');
            $table->foreignId('sub_category_id')->nullable()->constrained('erp_item_sub_categories');
            $table->string('item_description')->nullable();
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
        Schema::dropIfExists('erp_items');
    }
};
