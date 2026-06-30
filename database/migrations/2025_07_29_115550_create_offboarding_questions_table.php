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
        Schema::create('offboarding_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_value_id')->nullable()->constrained('type_values');
            $table->string('question');
            $table->enum('type', ['text', 'boolean']);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offboarding_questions');
    }
};
