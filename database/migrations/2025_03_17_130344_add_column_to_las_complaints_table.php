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
        Schema::table('las_complaints', function (Blueprint $table) {
            $table->string('other_specify')->nullable();
            $table->dateTime('reminder_date_1')->nullable();
            $table->dateTime('reminder_date_2')->nullable();
            $table->dateTime('reminder_date_3')->nullable();
            $table->date('response_received_date')->nullable();
            $table->text('response_detail')->nullable();
            $table->date('res_forwarded_complainant_date')->nullable();
            $table->string('response_mode')->nullable();
            $table->string('response_of_feedback_giver')->nullable();
            $table->integer('complaint_status')->nullable();
            $table->date('closing_date')->nullable();
            $table->text('remarks')->nullable();
            $table->integer('no_days_feedback_closed')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('las_complaints', function (Blueprint $table) {
            $table->dropColumn('other_specify');
            $table->dropColumn('reminder_date_1');
            $table->dropColumn('reminder_date_2');
            $table->dropColumn('reminder_date_3');
            $table->dropColumn('response_received_date');
            $table->dropColumn('response_detail');
            $table->dropColumn('res_forwarded_complainant_date');
            $table->dropColumn('response_mode');
            $table->dropColumn('response_of_feedback_giver');
            $table->dropColumn('complaint_status');
            $table->dropColumn('closing_date');
            $table->dropColumn('remarks');
            $table->dropColumn('no_days_feedback_closed');
        });
    }
};
