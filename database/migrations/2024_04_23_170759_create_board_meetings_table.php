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
        if (!Schema::hasTable('board_meetings')) {
            Schema::create('board_meetings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('agenda_id')->nullable()->constrained('board_meeting_agendas');
                $table->string('board_meeting_title')->nullable();
                $table->text('description')->nullable();
                $table->date('board_meeting_date')->nullable();
                $table->string('board_meeting_time')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->foreignId('updated_by')->nullable()->constrained('users');
                $table->softDeletes();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('board_meetings');
    }
};
