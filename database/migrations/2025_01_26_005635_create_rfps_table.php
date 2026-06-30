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
        Schema::create('rfps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->nullable()->constrained('opportunities');
            $table->string('rfp_series')->nullable();
            $table->foreignId('rfp_status')->nullable()->constrained('type_values');
            $table->date('date')->nullable();
            $table->date('valid_till')->nullable();
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
        Schema::dropIfExists('rfps');
    }
};
