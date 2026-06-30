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
        Schema::table('sub_grant_appreciations', function (Blueprint $table) {
            $table->foreignId('sub_grant_id')->nullable()->constrained('sub_grants');
            $table->string('name')->nullable();
            $table->integer('draft_by')->nullable();
            $table->string('attachment')->nullable();
            $table->text('remarks')->nullable();
            $table->integer('status')->nullable();
            $table->integer('approval_status')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sub_grant_appreciations', function (Blueprint $table) {

        });
    }
};
