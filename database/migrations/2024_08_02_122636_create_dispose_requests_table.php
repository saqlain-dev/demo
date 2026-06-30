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
        Schema::create('dispose_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('project_profiles');
            $table->foreignId('department_id')->nullable()->constrained('type_values');
            $table->date('date')->nullable();
            $table->string('dispose_request_no')->nullable();
            $table->text('description')->nullable();
            $table->text('purpose')->nullable();
            $table->text('remarks')->nullable();

            $table->unsignedTinyInteger('pr_status')->nullable();
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
        Schema::dropIfExists('dispose_requests');
    }
};
