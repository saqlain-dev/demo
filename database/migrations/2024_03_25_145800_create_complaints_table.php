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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('complaint_no')->nullable();
            $table->date('complaint_date')->nullable();
            $table->string('name')->nullable();
            $table->foreignId('department')->nullable()->constrained('type_values');
            $table->string('position_title')->nullable();
            $table->string('contact_detail')->nullable();
            $table->string('complaint_against')->nullable();
            $table->foreignId('nature_of_complaint')->nullable()->constrained('type_values');
            $table->text('complaint_detail')->nullable();
            $table->string('complaint_file')->nullable();
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
        Schema::dropIfExists('complaints');
    }
};
