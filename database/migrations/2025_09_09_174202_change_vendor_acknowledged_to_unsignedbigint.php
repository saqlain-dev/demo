<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tables and their columns to update
     */
    protected array $tables = [
        'work_orders' => 'vendor_acknowledged',
        'purchase_orders' => 'vendor_acknowledged',
        'consultant_contracts' => 'acknowledgment',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $tableName => $columnName) {
            $tmpColumn = $columnName . '_tmp';

            // Add temporary column
            Schema::table($tableName, function (Blueprint $table) use ($tmpColumn) {
                $table->unsignedBigInteger($tmpColumn)->default(0);
            });

            // Copy values
            DB::table($tableName)->update([
                $tmpColumn => DB::raw($columnName)
            ]);

            // Drop old column
            Schema::table($tableName, function (Blueprint $table) use ($columnName) {
                $table->dropColumn($columnName);
            });

            // Rename tmp column back
            Schema::table($tableName, function (Blueprint $table) use ($tmpColumn, $columnName) {
                $table->renameColumn($tmpColumn, $columnName);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $tableName => $columnName) {
            $tmpColumn = $columnName . '_tmp';

            // Add temporary column
            Schema::table($tableName, function (Blueprint $table) use ($tmpColumn) {
                $table->boolean($tmpColumn)->default(false);
            });

            // Copy values
            DB::table($tableName)->update([
                $tmpColumn => DB::raw($columnName)
            ]);

            // Drop old column
            Schema::table($tableName, function (Blueprint $table) use ($columnName) {
                $table->dropColumn($columnName);
            });

            // Rename tmp column back
            Schema::table($tableName, function (Blueprint $table) use ($tmpColumn, $columnName) {
                $table->renameColumn($tmpColumn, $columnName);
            });
        }
    }
};
