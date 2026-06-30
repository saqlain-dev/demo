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
        Schema::create('item_variants', function (Blueprint $table) {
            $table->id();
            $table->string('serial_no')->nullable();
            $table->foreignId('item_id')->nullable()->constrained('items');
            $table->foreignId('assign_to_emp')->nullable()->constrained('employees');
            $table->foreignId('assign_to_dept')->nullable()->constrained('type_values');
            $table->unsignedTinyInteger('inventory_type')->default(0);
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
        Schema::dropIfExists('item_variants');
    }
};
