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
        Schema::create('yearly_leave_entitlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_type_id')->nullable()->constrained('type_values');
            $table->unsignedSmallInteger('yearly_entitlement_year')->nullable();
            $table->unsignedSmallInteger('yearly_entitlement_leave')->nullable();
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
        Schema::dropIfExists('yearly_leave_entitlements');
    }
};
