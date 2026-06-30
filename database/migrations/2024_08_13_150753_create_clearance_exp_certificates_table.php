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
        Schema::create('clearance_exp_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_offboarding_id')->nullable()->constrained('employee_offboardings');
            $table->foreignId('certificate_type')->nullable()->constrained('type_values');
            $table->date('date')->nullable();
            $table->string('name')->nullable();
            $table->text('particular')->nullable();
            $table->text('remarks')->nullable();
            $table->string('attachment')->nullable();
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
        Schema::dropIfExists('clearance_exp_certificates');
    }
};
