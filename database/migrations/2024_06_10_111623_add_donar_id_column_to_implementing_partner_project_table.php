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
        Schema::table('implementing_partner_project', function (Blueprint $table) {
            $table->dropForeign(['partner_id']);
            $table->foreignId('partner_id')->nullable()->change();
            $table->foreignId('donor_id')->nullable()->constrained('project_implementing_partners');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('implementing_partner_project', function (Blueprint $table) {
            $table->dropForeign(['donor_id']);
            $table->dropColumn('donar_id');
        });
    }
};
