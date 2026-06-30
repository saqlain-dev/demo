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
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('series')->nullable();
            $table->foreignId('opportunity_type')->nullable()->constrained('type_values');
            $table->foreignId('sales_stage')->nullable()->constrained('type_values');
            $table->unsignedBigInteger('opportunityable_id'); // Polymorphic ID
            $table->string('opportunityable_type');
            $table->string('source')->nullable();
            $table->date('closing_date')->nullable();
            $table->string('party')->nullable();
            $table->foreignId('opportunity_owner')->nullable()->constrained('employees');
            $table->float('probability')->nullable();
            $table->foreignId('opportunity_status')->nullable()->constrained('type_values');
            $table->string('currency')->nullable();
            $table->double('opportunity_amount',18,2)->nullable();
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
        Schema::dropIfExists('opportunities');
    }
};
