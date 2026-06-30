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
        Schema::create('event_management_vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_management_id')->nullable()->constrained('event_management');
            $table->foreignId('vendor_id')->nullable()->constrained('vendors');
            $table->tinyInteger('isApplied')->default(0);
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
        Schema::dropIfExists('event_management_vendors');
    }
};
