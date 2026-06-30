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
        Schema::create('communication_event_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('event_categories');
            $table->foreignId('sub_category_id')->nullable()->constrained('event_sub_categories');
            $table->foreignId('department_id')->nullable()->constrained('type_values');

            $table->string('event_name')->nullable();
            $table->string('size')->nullable();
            $table->string('color_scheme')->nullable();
            $table->string('quantity')->nullable();
            $table->decimal('budget', 18)->nullable();
            $table->text('other_requirements')->nullable();

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
        Schema::dropIfExists('communication_event_details');
    }
};
