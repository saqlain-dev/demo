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
        Schema::create('tax_settings', function (Blueprint $table) {
            $table->id();
            $table->string('tax_name')->nullable();
            $table->foreignId('tax_type')->nullable()->constrained('type_values');
            $table->foreignId('tax_computation')->nullable()->constrained('type_values');
            $table->foreignId('tax_scope')->nullable()->constrained('type_values');
            $table->string('label_on_invoice')->nullable();
            $table->text('description')->nullable();
            $table->float('filer')->nullable();
            $table->float('non_filer')->nullable();
            $table->float('late_filer')->nullable();
            $table->foreignId('tax_group')->nullable()->constrained('type_values');
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
        Schema::dropIfExists('tax_settings');
    }
};
