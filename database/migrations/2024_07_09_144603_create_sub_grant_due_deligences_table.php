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
        Schema::create('sub_grant_due_deligences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_grant_id')->nullable()->constrained('sub_grants');
            $table->string('name')->nullable();
            $table->text('remarks')->nullable();
            $table->date('date')->nullable();
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
        Schema::dropIfExists('sub_grant_due_deligences');
    }
};
