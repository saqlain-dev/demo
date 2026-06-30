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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('lead_series')->nullable();
            $table->string('job_title')->nullable();
            $table->foreignId('lead_owner')->nullable()->constrained('employees');
            $table->foreignId('salutation')->nullable()->constrained('type_values');
            $table->foreignId('gender')->nullable()->constrained('type_values');
            $table->foreignId('lead_status')->nullable()->constrained('type_values');
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('source')->nullable();
            $table->foreignId('lead_type')->nullable()->constrained('type_values');
            $table->foreignId('lead_request_type')->nullable()->constrained('type_values');
            $table->string('email')->nullable();
            $table->string('mobile_no')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('phone_ext')->nullable();
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
        Schema::dropIfExists('leads');
    }
};
