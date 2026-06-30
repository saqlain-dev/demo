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
        Schema::create('gdn_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gdn_id')->nullable()->constrained('gdns');
            $table->text('gdn_description')->nullable();
            $table->integer('required_quantity')->nullable();
            $table->string('unit_of_measurement')->nullable();
            $table->decimal('unit_price',16,2)->nullable();
            $table->foreignId('item_id')->nullable()->constrained('items');
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
        Schema::dropIfExists('gdn_items');
    }
};
