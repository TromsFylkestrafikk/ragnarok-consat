<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Columns for stop place ID and name moved.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consat_calls', function (Blueprint $table) {
            $table->char('nsr_stop_id', 64)->after('sequence')->comment('NSR quay ID');
            $table->string('stop_name')->after('nsr_stop_id')->comment('NSR quay name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('consat_calls', function (Blueprint $table) {
            $table->dropColumn(['nsr_stop_id', 'stop_name']);
        });
    }
};
