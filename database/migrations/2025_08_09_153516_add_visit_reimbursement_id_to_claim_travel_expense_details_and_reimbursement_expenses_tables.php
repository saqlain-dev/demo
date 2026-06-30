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
        Schema::table('claim_travel_expense_details', function (Blueprint $table) {
            $table->foreignId('visit_reimbursement_id')
                ->nullable()
                ->constrained('visit_reimbursements')
                ->after('claim_travel_expense_id'); // Adjust position if needed
        });

        Schema::table('reimbursement_expenses', function (Blueprint $table) {
            $table->foreignId('visit_reimbursement_id')
                ->nullable()
                ->constrained('visit_reimbursements')
                ->after('reimbursement_id'); // Adjust position if needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('claim_travel_expense_details', function (Blueprint $table) {
            $table->dropForeign(['visit_reimbursement_id']);
            $table->dropColumn('visit_reimbursement_id');
        });

        Schema::table('reimbursement_expenses', function (Blueprint $table) {
            $table->dropForeign(['visit_reimbursement_id']);
            $table->dropColumn('visit_reimbursement_id');
        });
    }
};
