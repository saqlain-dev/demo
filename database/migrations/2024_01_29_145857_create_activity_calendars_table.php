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
        Schema::create('activity_calendars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('project_profiles');
            $table->foreignId('activity_id')->nullable()->constrained('activities');
            $table->string('audience')->nullable();
            $table->string('facilitator')->nullable();
            $table->string('venue')->nullable();
            $table->date('date')->nullable();

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
        Schema::dropIfExists('activity_calendars');
    }
};
