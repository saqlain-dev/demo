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
        Schema::create('board_resolution_passeds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_meeting_id')->nullable()->constrained('board_meetings');
            $table->string('name')->nullable();
            $table->date('date')->nullable();
            $table->text('particular')->nullable();
            $table->text('remarks')->nullable();
            $table->string('attachment')->nullable();
            $table->integer('status')->default(1);
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
        Schema::dropIfExists('board_resolution_passeds');
    }
};
