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
        Schema::create('salary_account_head_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_head_id')->nullable()->constrained('chart_of_accounts');
            $table->foreignId('allowance_deduction_id')->nullable()->constrained('allowance_deductions');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_account_head_settings');
    }
};
