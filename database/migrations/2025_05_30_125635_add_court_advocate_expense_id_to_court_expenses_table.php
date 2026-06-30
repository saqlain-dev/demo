<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('court_expenses', function (Blueprint $table) {
            $table->foreignId('court_advocate_expense_id')
                  ->nullable()
                  ->constrained('court_advocate_expenses')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('court_expenses', function (Blueprint $table) {
            $table->dropForeign(['court_advocate_expense_id']);
            $table->dropColumn('court_advocate_expense_id');
        });
    }
};