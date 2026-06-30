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
        Schema::create('grant_fund_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nofo_id')->nullable()->constrained('nofos');
            $table->string('invoice')->nullable();
            $table->string('attachment')->nullable();
            $table->date('date')->nullable();
            $table->integer('status')->nullable();
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
        Schema::dropIfExists('grant_fund_requests');
    }
};
