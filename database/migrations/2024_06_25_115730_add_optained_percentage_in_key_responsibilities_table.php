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
        Schema::table('key_responsibilities', function (Blueprint $table) {
            $table->string('obtained_percentage')->nullable();
            $table->string('obtained_rating')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('key_responsibilities', function (Blueprint $table) {
            //
        });
    }
};
