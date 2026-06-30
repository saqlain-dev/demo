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
        Schema::create('event_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_management_id')->nullable()->constrained('event_management');
            $table->date('task_date')->nullable();
            $table->foreignId('assign_to')->nullable()->constrained('users');
            $table->foreignId('flag_status')->nullable()->constrained('type_values');
            $table->foreignId('task_status')->nullable()->constrained('type_values');
            $table->string('attachment')->nullable();
            $table->text('description')->nullable();
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
        Schema::dropIfExists('event_tasks');
    }
};
