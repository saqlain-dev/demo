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
        Schema::table('procurement_details', function (Blueprint $table) {
            $table->text('audit_remarks')->nullable();
            $table->tinyInteger('audit_status')->nullable();
            $table->integer('audit_updated_by')->nullable();
            $table->dateTime('audit_updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('procurement_details', function (Blueprint $table) {
            $table->dropColumn('audit_remarks');
            $table->dropColumn('audit_status');
            $table->dropColumn('audit_updated_by');
            $table->dropColumn('audit_updated_at');
        });
    }
};
