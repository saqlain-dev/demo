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
        Schema::create('incident_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles');
            $table->integer('report_type')->nullable();
            $table->integer('is_notifiable')->nullable();
            $table->string('reporting_station')->nullable();
            $table->string('incident_location')->nullable();
            $table->date('incident_date')->nullable();
            $table->time('incident_time')->nullable();
            $table->dateTime('reporting_date')->nullable();
            $table->integer('chauffeur_id')->nullable();
            $table->string('accident_report_no')->nullable();
            $table->string('incident_reporting_person')->nullable();
            $table->text('nature_of_damage')->nullable();
            $table->text('nature_of_injuries')->nullable();
            $table->text('accident_description')->nullable();
            $table->text('human_injuries_description')->nullable();
            $table->text('third_party_involvement')->nullable();
            $table->text('investigation_findings')->nullable();
            $table->text('recommendation')->nullable();
            $table->tinyInteger('is_payment_made')->nullable();
            $table->tinyInteger('is_payment_received')->nullable();
            $table->tinyInteger('status')->default(STATUS::PENDING);
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
        Schema::dropIfExists('incident_reports');
    }
};
