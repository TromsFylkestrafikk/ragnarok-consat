<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * NSR quay info mapped into the consat_calls table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consat_calls', function (Blueprint $table) {
            $table->char('nsr_quay_id', 64)->after('sequence')->comment('NSR quay ID');
            $table->string('stop_name')->after('nsr_quay_id')->comment('Name of stop');
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
            $table->dropColumn(['nsr_quay_id', 'stop_name']);
        });
    }
};
