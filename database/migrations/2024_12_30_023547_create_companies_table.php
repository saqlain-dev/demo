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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->nullable();
            $table->string('letter_head')->nullable();
            $table->string('abbr')->nullable();
            $table->string('tax_ID')->nullable();
            $table->foreignId('currency_id')->nullable()->constrained('type_values');
            $table->string('domain')->nullable();
            $table->foreignId('country_id')->nullable()->constrained('countries');
            $table->date('date_of_establishment')->nullable();
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
        Schema::dropIfExists('companies');
    }
};
