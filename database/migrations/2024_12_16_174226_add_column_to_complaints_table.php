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


                $table->dropColumn('nature_of_complaint');
        });
        Schema::table('complaints', function (Blueprint $table) {


            $table->foreignId('nature_of_complaint')->nullable()->constrained('type_values');
        });
        Schema::table('complaints', function (Blueprint $table) {
            // Change the column type
            $table->renameColumn('complain_against','complain_type')->change();
        });
        Schema::table('complaints', function (Blueprint $table) {
            // Change the column type
            $table->string('other_nature')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->renameColumn('complain_type','complain_against')->change();
            $table->dropColumn('other_nature');
        });
        Schema::table('complaints', function (Blueprint $table) {
            // Drop the foreign key constraint by its name
            $table->dropForeign('nature_of_complaint');
        });
    }
};
