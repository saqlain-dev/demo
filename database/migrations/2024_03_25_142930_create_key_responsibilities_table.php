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
        Schema::create('key_responsibilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('performance_planning_id')->nullable()->constrained('performance_plannings');
            $table->date('date_modified')->nullable();
            $table->text('key_responsibility')->nullable();
            $table->string('priority')->nullable();
            $table->unsignedTinyInteger('total_marks')->default(10);
            $table->unsignedTinyInteger('awarded_marks')->nullable();
            $table->unsignedTinyInteger('supervisor_rating')->nullable();
            $table->text('remarks')->nullable();

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
        Schema::dropIfExists('key_responsibilities');
    }
};
