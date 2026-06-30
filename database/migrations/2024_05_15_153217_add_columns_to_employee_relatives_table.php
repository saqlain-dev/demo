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
        Schema::table('employee_relatives', function (Blueprint $table) {
            $table->integer('file_type')->nullable();
            $table->string('relationship_proof')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_relatives', function (Blueprint $table) {
            $table->dropColumn('file_type');
            $table->dropColumn('relationship_proof');
        });
    }
};
