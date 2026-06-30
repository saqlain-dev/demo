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
        Schema::create('due_delegence_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('due_delegence_id')->nullable()->constrained('due_delegences');
            $table->foreignId('nofo_detail_id')->nullable()->constrained('nofo_details');
            $table->string('attachment')->nullable();
            $table->text('remarks')->nullable();
            $table->integer('upload_status')->nullable();
            $table->integer('status')->nullable();
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
        Schema::dropIfExists('due_delegence_details');
    }
};
