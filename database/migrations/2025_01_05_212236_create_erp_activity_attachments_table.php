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
        Schema::create('erp_activity_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('erp_activity_id')->nullable()->constrained('erp_activities');
            $table->string('attachment_file')->nullable();
            $table->foreignId('attachment_type')->nullable()->constrained('type_values');
            $table->string('attachment_name')->nullable();
            $table->text('description')->nullable();
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
        Schema::dropIfExists('erp_activity_attachments');
    }
};
