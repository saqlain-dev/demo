<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('court_advocate_expenses', function (Blueprint $table) {
            $table->integer('approval_status')->default(4);
        });
    }

    public function down()
    {
        Schema::table('court_advocate_expenses', function (Blueprint $table) {
            $table->dropColumn('approval_status');
        });
    }
};
