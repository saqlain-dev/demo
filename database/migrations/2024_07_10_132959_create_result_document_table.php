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
        Schema::create('result_documents', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->foreignId('worksheet_id')->nullable()->constrained('financial_analysis_work_sheets');
            $table->string('attachment')->nullable();
            $table->foreignId('prepared_by')->nullable();
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
        Schema::dropIfExists('result_documents');
    }
};
