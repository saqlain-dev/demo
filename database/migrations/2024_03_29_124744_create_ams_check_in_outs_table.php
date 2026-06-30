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
        Schema::create('ams_check_in_outs', function (Blueprint $table) {
            $table->id('CheckInOutID');
            $table->integer('USERID')->nullable();
            $table->unsignedTinyInteger('VERIFYCODE')->nullable();
            $table->unsignedTinyInteger('SENSORID')->nullable();
            $table->date('att_date')->nullable();
            $table->dateTime('att_timeIn')->nullable();
            $table->dateTime('att_TimeOut')->nullable();
            $table->string('ShiftStartTime')->nullable();
            $table->string('ShiftEndTime')->nullable();
            $table->unsignedTinyInteger('SensorIDOut')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ams_check_in_outs');
    }
};
