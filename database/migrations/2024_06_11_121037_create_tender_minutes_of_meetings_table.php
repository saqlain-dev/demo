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
        Schema::create('tender_minutes_of_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tender_id')->nullable()->constrained('tenders');
            $table->text('tender_mom_detail')->nullable();
            $table->string('tender_mom_document')->nullable();
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
        Schema::dropIfExists('tender_minutes_of_meetings');
    }
};
