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
        Schema::table('approval_process_names', function (Blueprint $table) {
            $table->string('category')->nullable();
            $table->foreignId('email_template_id')->nullable()->constrained('email_templates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_process_names', function (Blueprint $table) {
            $table->dropColumn('category');
            $table->dropForeign(['email_template_id']);
            $table->dropColumn('email_template_id');
        });
    }
};
