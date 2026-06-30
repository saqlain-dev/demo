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
        Schema::table('check_in_sheets', function (Blueprint $table) {
            $table->foreignId('activitie_id')->nullable()->constrained('activities');
            $table->unsignedTinyInteger('indicator_id')->nullable();
            $table->string('target')->nullable();
            $table->string('assignee')->nullable();
            $table->string('achieved')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('check_in_sheets', function (Blueprint $table) {
            //
        });
    }
};
