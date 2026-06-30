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
        Schema::table('opportunities', function (Blueprint $table) {
            $table->decimal('quoted_value',18,2)->nullable();
            $table->foreignId('priority_level')->nullable()->constrained('type_values');
            $table->date('proposal_submission_date')->nullable();
            $table->date('presale_submission_date')->nullable();
            $table->foreignId('division_id')->nullable()->constrained('divisions');
            $table->string('contractor')->nullable();
            $table->foreignId('stage_rating')->nullable()->constrained('scale_ratings');
            $table->string('principals_involved')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropForeign(['priority_level']);
            $table->dropForeign(['division_id']);
            $table->dropForeign(['stage_rating']);

            $table->dropColumn('priority_level');
            $table->dropColumn('division_id');
            $table->dropColumn('stage_rating');
            $table->dropColumn('quoted_value');
            $table->dropColumn('proposal_submission_date');
            $table->dropColumn('presale_submission_date');
            $table->dropColumn('contractor');
            $table->dropColumn('principals_involved');
        });
    }
};
