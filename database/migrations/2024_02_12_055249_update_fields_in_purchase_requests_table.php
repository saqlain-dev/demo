<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['sub_category_id']);
            \App\Models\PurchaseRequest::query()->update(['category_id' => null, 'sub_category_id' => null]);
            $table->dropColumn(['category_id', 'sub_category_id']);

            $table->foreignId('project_id')->nullable()->constrained('project_profiles');
            $table->foreignId('department_id')->nullable()->constrained('type_values');
            $table->date('date')->nullable();
            $table->string('purchase_request_no')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            //
        });
    }
};
