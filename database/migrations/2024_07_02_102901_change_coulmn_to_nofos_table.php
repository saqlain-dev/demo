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
        Schema::table('nofos', function (Blueprint $table) {
            $table->renameColumn('org_type', 'donor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nofos', function (Blueprint $table) {
            $table->renameColumn('donor_id', 'org_type');
        });
    }
};
