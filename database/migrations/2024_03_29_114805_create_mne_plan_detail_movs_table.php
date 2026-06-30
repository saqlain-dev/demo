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
        Schema::create('mne_plan_detail_movs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mne_plan_detail_id')->nullable()->constrained('mne_plan_details');
            $table->integer('movs_id')->nullable();
            $table->string('mov_file')->nullable();
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
        Schema::dropIfExists('indicator_progress_movs');
    }
};
