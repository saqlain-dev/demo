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
            $table->boolean('is_system_generated')->default(0);
            $table->string('attachment')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('generated_letters', function (Blueprint $table) {
            $table->dropColumn('is_system_generated');
            $table->dropColumn('attachment');
        });
    }
};
