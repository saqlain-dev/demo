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
        Schema::create('meeting_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained('employees');
            $table->string('meeting_title');
            $table->foreignId('meeting_room_id')->nullable()->constrained('type_values');
            $table->date('meeting_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('attendees_count')->default(0);
            $table->text('meeting_notes')->nullable();
            $table->integer('approval_status')->default(4); 
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
        Schema::dropIfExists('meeting_bookings');
    }
};
