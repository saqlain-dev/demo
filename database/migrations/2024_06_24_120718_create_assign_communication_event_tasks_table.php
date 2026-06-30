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
        Schema::create('assign_communication_event_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->nullable()->constrained('communication_events');
            $table->foreignId('task_id')->nullable()->constrained('communication_event_details');
            $table->foreignId('assigned_by')->nullable()->constrained('employees');
            $table->foreignId('assigned_to')->nullable()->constrained('employees');
            $table->date('deadline')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assign_communication_event_tasks');
    }
};
