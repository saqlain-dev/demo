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
        Schema::table('recruitment_plans', function (Blueprint $table) {
            $table->double('sub_total')->nullable();
        });

        Schema::table('employee_requisitions', function (Blueprint $table) {
            $table->integer('is_consultant')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_plans', function (Blueprint $table) {
            $table->dropColumn('sub_total');
        });

        Schema::table('employee_requisitions', function (Blueprint $table) {
            $table->dropColumn('is_consultant');
        });
    }
};
