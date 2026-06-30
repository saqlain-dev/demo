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
        Schema::create('employee_claim_reimbursements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_insurance_id')->nullable()->constrained();
            $table->integer('amount_claim')->nullable();
            $table->date('submission_date')->nullable();
            $table->date('reimbursement_date')->nullable();
            $table->text('comments')->nullable();
            $table->string('attachment')->nullable();

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
        Schema::dropIfExists('employee_claim_reimbursements');
    }
};
