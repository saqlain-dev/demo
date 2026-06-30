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
        Schema::create('exit_employee_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained('employees');
            $table->string('employee_type')->nullable();
            $table->string('remarks')->nullable();
            $table->string('leaving_reason')->nullable();
            $table->string('reason_details')->nullable();
            $table->string('date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('exit_employee_detail_id')->nullable()->constrained('exit_employee_details');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exit_employee_details');

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('exit_employee_detail_id');
        });
    }
};
