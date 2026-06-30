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
        Schema::create('currency_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('currency_id')->nullable()->constrained('currencies');
            $table->date('date')->nullable();
            $table->decimal('unit_per_pkr', 8, 2)->nullable();
            $table->decimal('pkr_per_unit', 8, 2)->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('currencies', function (Blueprint $table) {
            $table->dropColumn('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_details');

        Schema::table('currencies', function (Blueprint $table) {
            $table->date('date')->nullable();
        });
    }
};
