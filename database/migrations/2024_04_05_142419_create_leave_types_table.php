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
        Schema::create('LeaveTypes', function (Blueprint $table) {
            $table->unsignedBigInteger('LeaveTypeID')->primary();
            $table->string('Description')->nullable();
            $table->boolean('WithoutBalance')->nullable();
            $table->boolean('SandwichRule')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('LeaveTypes');
    }
};
