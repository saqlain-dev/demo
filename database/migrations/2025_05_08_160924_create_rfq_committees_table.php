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
        Schema::create('rfq_committees', function (Blueprint $table) {
            $table->id();
            $table->text('comments')->nullable();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->foreignId('pr_rfq_id')->nullable()->constrained('purchase_request_rfqs');
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
        Schema::dropIfExists('rfq_committees');
    }
};
