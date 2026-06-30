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
        if (!Schema::hasTable('board_meeting_agendas')) {
            Schema::create('board_meeting_agendas', function (Blueprint $table) {
                $table->id();
                $table->string('agenda_title')->nullable();
                $table->text('agenda_description')->nullable();
                $table->string('agenda_category')->nullable();
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
        Schema::dropIfExists('board_meeting_agendas');
    }
};
