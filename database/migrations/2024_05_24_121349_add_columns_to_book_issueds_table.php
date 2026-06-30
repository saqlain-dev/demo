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
        Schema::table('book_issueds', function (Blueprint $table) {
            $table->foreignId('book_request_id')->nullable()->constrained('book_requests');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('book_issueds', function (Blueprint $table) {
            $table->dropColumn('book_request_id');
        });
    }
};
