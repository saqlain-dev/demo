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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('asset_type')->nullable();
            $table->string('gl_code')->nullable();
            $table->text('description')->nullable();
            $table->string('serial_no')->nullable();
            $table->string('inventory_no')->nullable();
            $table->string('new_inventory_no')->nullable();
            $table->foreignId('asset_category')->nullable()->constrained('item_sub_categories');
            $table->string('asset_location')->nullable();
            $table->foreignId('handover_to')->nullable()->constrained('employees');
            $table->date('date_of_purchase')->nullable();
            $table->decimal('cost_of_items',18,2)->nullable();
            $table->string('voucher_no')->nullable();
            $table->date('voucher_date')->nullable();
            $table->string('vendor_name')->nullable();
            $table->foreignId('project_id')->nullable()->constrained('project_profiles');

            $table->string('depreciation_rate')->nullable();
            $table->date('acc_dep_start_date')->nullable();
            $table->date('acc_dep_end_date')->nullable();
            $table->string('number_of_months')->nullable();
            $table->decimal('net_book_value',18,2)->nullable();
            $table->string('item_sold')->nullable();
            $table->date('physical_verification_date')->nullable();
            $table->text('remarks')->nullable();

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
        Schema::dropIfExists('assets');
    }
};
