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
            Schema::table('employee_manuel_attendances', function (Blueprint $table) {
                // Drop the foreign key constraint by its name
                $table->dropForeign('employee_manuel_attendances_userid_foreign');
            });

            Schema::table('employee_manuel_attendances', function (Blueprint $table) {


                $table->dropColumn('userid');
            });
            Schema::table('employee_manuel_attendances', function (Blueprint $table) {


                $table->foreignId('userid')->nullable()->constrained('employees');
            });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_manuel_attendances', function (Blueprint $table) {
            //
        });
    }
};
