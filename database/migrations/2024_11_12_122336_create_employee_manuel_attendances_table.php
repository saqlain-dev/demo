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
        Schema::create('employee_manuel_attendances', function (Blueprint $table) {
            $table->id();
            $table->date('att_date')->nullable();
            $table->dateTime('att_timeIn')->nullable();
            $table->dateTime('att_timeOut')->nullable();
            $table->foreignId('userid')->nullable()->constrained('employees');
            $table->unsignedTinyInteger('approval_status')->default(STATUS::DRAFT);
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_manuel_attendances');
    }
};
