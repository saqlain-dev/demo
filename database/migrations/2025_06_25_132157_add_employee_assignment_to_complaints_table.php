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
        Schema::table('complaints', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->unsignedTinyInteger('employee_status')->nullable();
            $table->text('employee_remarks')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropColumn(['employee_id', 'employee_status', 'employee_remarks']);
        });
    }

};
