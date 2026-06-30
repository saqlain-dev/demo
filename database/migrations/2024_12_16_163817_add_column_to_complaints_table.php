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
        Schema::table('complaints', function (Blueprint $table) {
            // Drop the foreign key constraint by its name
            $table->dropForeign('complaints_nature_of_complaint_foreign');
        });

        Schema::table('complaints', function (Blueprint $table) {
            // Change the column type
            $table->string('nature_of_complaint')->change();
        });

        Schema::table('complaints', function (Blueprint $table) {
            // Add the new column
            $table->unsignedTinyInteger('complain_against')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn('complain_against');
            $table->integer('nature_of_complaint')->change();
        });
    }
};
