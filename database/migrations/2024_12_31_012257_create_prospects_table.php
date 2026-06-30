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
        Schema::create('prospects', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->nullable();
            $table->foreignId('market_segment_id')->nullable()->constrained('type_values');
            $table->string('prospect_owner')->nullable();
            $table->foreignId('customer_group_id')->nullable()->constrained('type_values');
            $table->foreignId('industry_id')->nullable()->constrained('type_values');
            $table->string('website')->nullable();
            $table->unsignedTinyInteger('no_of_employees')->default(0);
            $table->foreignId('territory_id')->nullable()->constrained('type_values');
            $table->string('fax')->nullable();
            $table->double('annual_revenue',18,2)->nullable();
            $table->foreignId('company_id')->nullable()->constrained('companies');
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
        Schema::dropIfExists('prospects');
    }
};
