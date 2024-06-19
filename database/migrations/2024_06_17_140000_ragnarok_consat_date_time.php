<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Re-structure keys / primary key.
        Schema::table('consat_call_details', function (Blueprint $table) {
            $table->dropPrimary(['call_id', 'timestamp']);
        });
        $this->timestampToDatetime('consat_call_details', 'timestamp', 'Timestamp of event. Date + Time', false);
        $this->timestampToDatetime('consat_passenger_count', 'timestamp', 'Timestamp of pax count. Date + Time', false);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

    /**
     * @see TromsFylkestrafikk/ragnarok-fara/database/migrations/2024_06_14_130000_ragnarok_fara_date_time.php
     */
    protected function timestampToDatetime(string $tableName, string $columnName, string $comment, $nullable = true)
    {
        DB::statement(sprintf('alter table %s rename column %s to %s', $tableName, $columnName, $columnName . '_old'));
        Schema::table($tableName, function (Blueprint $table) use ($columnName, $comment) {
            $table->dateTime($columnName)->nullable()->after($columnName . '_old')->comment($comment);
        });

        DB::table($tableName)->update([$columnName => DB::raw($columnName . '_old')]);

        if (!$nullable) {
            Schema::table($tableName, function (Blueprint $table) use ($columnName, $comment) {
                $table->dateTime($columnName)->nullable(false)->comment($comment)->change();
            });
        }

        Schema::table($tableName, function (Blueprint $table) use ($columnName) {
            $table->dropColumn($columnName . '_old');
        });
    }
};
