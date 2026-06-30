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
        Schema::create('air_travel_request_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('air_travel_requests');
            $table->date('date')->nullable();
            $table->string('departure_from')->nullable();
            $table->time('arrival_at')->nullable();
            $table->string('traveller_name')->nullable();
            $table->foreignId('department_id')->nullable()->constrained('type_values');
            $table->string('act_code',100)->nullable();
            $table->string('donor_code',100)->nullable();
            $table->text('purpose')->nullable();
            $table->decimal('estimated_amount',18)->nullable();

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
        Schema::dropIfExists('air_travel_requests');
    }
};
