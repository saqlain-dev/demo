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
        Schema::create('implementing_partner_project', function (Blueprint $table) {
            $table->foreignId('partner_id')->constrained('project_implementing_partners');
            $table->foreignId('project_id')->constrained('project_profiles');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('implementing_partner_project');
    }
};
