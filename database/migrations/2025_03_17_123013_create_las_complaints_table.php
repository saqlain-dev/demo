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
        Schema::create('las_complaints', function (Blueprint $table) {
            $table->id();
            $table->string('complainant_name')->nullable();
            $table->string('relation_with_applicant')->nullable();
            $table->dateTime('complaint_date')->nullable();
            $table->integer('gender')->nullable();
            $table->integer('district')->nullable();
            $table->text('address')->nullable();
            $table->string('contact')->nullable();
            $table->string('mode_of_feedback')->nullable();
            $table->string('feedback_received_by')->nullable();
            $table->string('relation_with_complainant')->nullable();
            $table->integer('complainant_category')->nullable();
            $table->integer('type_of_call')->nullable();
            $table->string('another_request')->nullable();
            $table->integer('feedback_category')->nullable();
            $table->text('detail_of_feedback')->nullable();
            $table->integer('priority')->nullable();
            $table->string('program')->nullable();
            $table->dateTime('forwarding_date')->nullable();
            $table->string('forwarding_mode')->nullable();
            $table->integer('forwarded_to')->nullable();
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
        Schema::dropIfExists('las_complaints');
    }
};
