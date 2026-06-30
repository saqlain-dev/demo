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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('project_profiles');
            $table->foreignId('province_id')->nullable()->constrained('type_values');
            $table->foreignId('category_id')->nullable()->constrained('type_values');
            $table->foreignId('theme_id')->nullable()->constrained('type_values');
            $table->string('lesson_title')->nullable();
            $table->text('lesson_narrative')->nullable();
            $table->text('recommendations')->nullable();
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
        Schema::dropIfExists('lessons');
    }
};
