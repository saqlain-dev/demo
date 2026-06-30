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
        Schema::create('stock_requests', function (Blueprint $table) {
            $table->id();
            $table->integer('department_id')->nullable();
            $table->integer('location_id')->nullable();
            $table->integer('requested_by')->nullable();
            $table->text('remarks')->nullable();
            $table->date('request_date')->nullable();
            $table->integer('status')->nullable();
            $table->unsignedTinyInteger('approval_status')->default(STATUS::DRAFT);
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
        Schema::dropIfExists('stock_requests');
    }
};
