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
        Schema::create('tbl_general_ledger_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('Gl_Id')->nullable()->constrained('tbl_general_ledgers');
            $table->string('VoucherID')->nullable();
            $table->string('VoucherType')->nullable();
            $table->date('Date')->nullable();
            $table->string('FinancialYear')->nullable();
            $table->string('NominalID')->nullable();
            $table->double('Credit',18,2)->nullable();
            $table->double('Debit',18,2)->nullable();
            $table->text('detail')->nullable();
            $table->string('CreatedBy')->nullable();
            $table->foreignId('project_id')->nullable()->constrained('project_profiles');
            $table->foreignId('vendor_id')->nullable()->constrained('vendors');
           // $table->foreignId('VerifiedBy')->nullable()->constrained('users');
            //$table->tinyInteger('IsVerified')->default(0);
            //$table->foreignId('PostedBy')->nullable()->constrained('users');
            //$table->tinyInteger('IsPosted')->default(0);
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
        Schema::dropIfExists('tbl_general_ledger_details');
    }
};
