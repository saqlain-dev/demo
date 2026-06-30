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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_order_no')->nullable();
            $table->date('purchase_order_date')->nullable();
            $table->foreignId('rfq_id')->nullable()->constrained('purchase_request_rfqs');
            $table->string('supplier')->nullable();
            $table->string('s_address')->nullable();
            $table->string('s_ntn_cnic')->nullable();
            $table->string('s_telephone')->nullable();
            $table->string('s_fax')->nullable();
            $table->string('s_email')->nullable();
            $table->string('s_contact')->nullable();

            $table->string('ship_to')->nullable();
            $table->string('ship_address')->nullable();
            $table->string('ship_telephone')->nullable();
            $table->string('ship_fax')->nullable();
            $table->string('ship_email')->nullable();
            $table->string('ship_contact')->nullable();

            $table->text('purpose_of_po')->nullable();
            $table->date('date_of_delivery')->nullable();
            $table->time('time_of_delivery')->nullable();

            $table->decimal('amount',18,2)->nullable();
            $table->float('tax')->nullable();
            $table->decimal('total',18,2)->nullable();

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
        Schema::dropIfExists('purchase_orders');
    }
};
