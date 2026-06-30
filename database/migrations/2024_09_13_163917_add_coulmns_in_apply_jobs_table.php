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
        Schema::table('apply_jobs', function (Blueprint $table) {
            $table->date('expected_joining_date')->nullable();
            $table->string('relocation')->nullable();
            $table->string('relationship_las')->nullable();
            $table->string('investigation')->nullable();
            $table->string('candidate_travel')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apply_jobs', function (Blueprint $table) {
            $table->dropColumn('expected_joining_date');
            $table->dropColumn('relocation');
            $table->dropColumn('relationship_las');
            $table->dropColumn('investigation');
            $table->dropColumn('candidate_travel');
        });
    }
};
