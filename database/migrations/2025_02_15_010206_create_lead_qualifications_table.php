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
        Schema::create('lead_qualifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained('leads');
            $table->tinyInteger('any_bid')->default(0);
            $table->decimal('amount',18,2)->nullable();
            $table->tinyInteger('commercial_compliance')->default(0);
            $table->text('reason')->nullable();
            $table->tinyInteger('competitors')->default(0);
            $table->string('contractor_type')->nullable();
            $table->tinyInteger('internal_reference')->default(0);
            $table->integer('employee_id')->nullable();
            $table->tinyInteger('delivery_material_available')->default(0);
            $table->dateTime('lead_time_delivery')->nullable();
            $table->decimal('bid_amount',18,2)->nullable();
            $table->text('justification')->nullable();
            $table->text('final_remarks')->nullable();
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
        Schema::dropIfExists('lead_qualifications');
    }
};
