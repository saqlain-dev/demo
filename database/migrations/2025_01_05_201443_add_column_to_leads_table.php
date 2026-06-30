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
        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('qualification_type')->nullable()->constrained('type_values');
            $table->foreignId('organization_id')->nullable()->constrained('companies');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['qualification_type']);
            $table->dropColumn('qualification_type');
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });
    }
};
