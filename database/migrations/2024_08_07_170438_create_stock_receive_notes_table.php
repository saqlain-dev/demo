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
        Schema::create('stock_receive_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_note_id')->nullable()->constrained('stock_transfer_notes');
            $table->date('receive_date')->nullable();
            $table->integer('receive_by')->nullable();
            $table->text('remarks')->nullable();
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
        Schema::dropIfExists('stock_receive_notes');
    }
};
