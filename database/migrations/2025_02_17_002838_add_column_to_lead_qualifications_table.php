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
        Schema::table('lead_qualifications', function (Blueprint $table) {
            $table->foreignId('qualification_status')->nullable()->constrained('type_values');
            $table->foreignId('qualified_by')->nullable()->constrained('employees');
            $table->date('qualified_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_qualifications', function (Blueprint $table) {
            $table->dropForeign(['qualification_status']);
            $table->dropColumn('qualification_status');

            $table->dropForeign(['qualified_by']);
            $table->dropColumn('qualified_by');

            $table->dropColumn('qualified_on');
        });
    }
};
