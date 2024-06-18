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
        $this->timestampToDatetime('consat_call_details', 'timestamp', 'Timestamp of event. Date + Time');
        $this->timestampToDatetime('consat_passenger_count', 'timestamp', 'Timestamp of pax count. Date + Time');
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
        Schema::table($tableName, function (Blueprint $table) use ($columnName){
            $table->renameColumn($columnName, $columnName . '_old');
        });

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
