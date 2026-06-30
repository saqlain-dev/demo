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
        Schema::table('generated_letters', function (Blueprint $table) {
            $table->integer('acknowledgment_status')->default(0);
            $table->date('date')->nullable();
            $table->text('remarks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('generated_letters', function (Blueprint $table) {
            $table->dropColumn('acknowledgment_status');
            $table->dropColumn('date');
            $table->dropColumn('remarks');
        });
    }
};
