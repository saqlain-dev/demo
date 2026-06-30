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
        Schema::create('donar_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('donar_name')->nullable();
            $table->string('donar_contact')->nullable();
            $table->string('donar_address')->nullable();
            $table->string('donar_logo')->nullable();
            $table->integer('approval_status')->default(STATUS::DRAFT);
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
        Schema::dropIfExists('donar_profiles');
    }
};
