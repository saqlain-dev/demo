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
        Schema::create('tax_management', function (Blueprint $table) {
            $table->id();
            $table->string('tax_name')->nullable();
            $table->integer('tax_type')->nullable();
            $table->integer('tax_computation')->nullable();
            $table->integer('tax_scope')->nullable();
            $table->integer('status')->default(1);
            $table->double('amount')->nullable();
            $table->string('filer_percentage')->nullable();
            $table->string('non_filer_percentage')->nullable();
            $table->string('late_filer_percentage')->nullable();
            $table->string('invoice_label')->nullable();
            $table->integer('tax_group')->nullable();
            $table->text('description')->nullable();
            $table->integer('country_id')->nullable();
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
        Schema::dropIfExists('tax_management');
    }
};
