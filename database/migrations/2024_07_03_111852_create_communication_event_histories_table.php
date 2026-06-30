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
        Schema::create('communication_event_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('communication_event_id')->nullable()->constrained();
            $table->foreignId('communication_event_detail_id')->nullable()->constrained();
            $table->unsignedTinyInteger('status')->default(0);
            $table->text('feedback')->nullable();
            $table->string('attachment')->nullable();
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
        Schema::dropIfExists('communication_event_histories');
    }
};
