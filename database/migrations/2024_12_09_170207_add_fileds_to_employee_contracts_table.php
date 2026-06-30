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
        Schema::create('parent_employee_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });
        
        Schema::table('employee_contracts', function (Blueprint $table) {
            $table->foreignId('parent_employee_contract_id')->nullable()->constrained('parent_employee_contracts');
            $table->foreignId('contract_type_id')->nullable()->constrained('type_values');
            $table->boolean('is_active')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_contracts', function (Blueprint $table) {
            $table->dropForeign(['parent_employee_contract_id']);
            $table->dropColumn('parent_employee_contract_id');
            
            $table->dropForeign(['contract_type_id']);
            $table->dropColumn('contract_type_id');
            
            $table->dropColumn('is_active');
        });

        Schema::dropIfExists('parent_employee_contracts');
    }
};
