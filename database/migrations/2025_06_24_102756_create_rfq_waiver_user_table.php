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
        Schema::create('rfq_waiver_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_waiver_id')->constrained('rfq_waivers');
            $table->foreignId('user_id')->constrained('users');
            $table->integer('is_approved')->default(4);
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfq_waiver_user');
    }
};
