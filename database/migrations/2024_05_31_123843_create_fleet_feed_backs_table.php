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
        Schema::create('fleet_feed_backs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requisition_id')->nullable()->constrained('vehicle_requests');
            $table->foreignId('question_id')->nullable()->constrained('feed_back_questions');
            $table->string('answer')->nullable();
            $table->string('type')->nullable();
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
        Schema::dropIfExists('fleet_feed_backs');
    }
};
