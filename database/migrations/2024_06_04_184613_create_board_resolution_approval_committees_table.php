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
        Schema::create('board_resolution_approval_committees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resolution_id')->nullable()->constrained('board_resolution_passeds');
            $table->foreignId('board_member_id')->nullable()->constrained('employees');
            $table->integer('status')->default(0);
            $table->text('description')->nullable();
            $table->dateTime('added_date')->nullable();
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
        Schema::dropIfExists('board_resolution_approval_committees');
    }
};
