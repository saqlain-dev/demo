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
        Schema::create('mne_observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('observation_sheet_id')->nullable()->constrained('observation_sheets');
            $table->text('observations')->nullable();
            $table->text('mitigation_on_spot')->nullable();
            $table->integer('type_of_red_flag')->nullable();
            $table->integer('priority')->nullable();
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
        Schema::dropIfExists('mne_observations');
    }
};
