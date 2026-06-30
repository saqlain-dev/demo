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
        // Types Table
        Schema::create('types', function (Blueprint $table) {
            $table->id();
            $table->string('key',150)->nullable();
            $table->string('name',150)->nullable();
            $table->boolean('status')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        //Type Values Table
        Schema::create('type_values', function (Blueprint $table) {
            $table->id();
            $table->string('name',150)->nullable();
            $table->foreignId('type_id')->nullable();
            $table->boolean('status')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('types');
        Schema::dropIfExists('type_values');
    }
};
