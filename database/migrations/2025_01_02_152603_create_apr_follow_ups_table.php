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
        Schema::create('apr_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_plan_report_id')->nullable()->constrained('audit_plan_reports');
            $table->date('deadline_date')->nullable();
            $table->text('observation')->nullable();
            $table->string('attachment')->nullable();
            $table->foreignId('apr_follow_up_status_id')->nullable()->constrained('type_values');
            
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
        Schema::dropIfExists('apr_follow_ups');
    }
};
