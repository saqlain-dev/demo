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
        Schema::create('project_profile_province', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_profile_id')->constrained('project_profiles');
            $table->foreignId('province_id')->constrained('provinces');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_profile_province');
    }
};
