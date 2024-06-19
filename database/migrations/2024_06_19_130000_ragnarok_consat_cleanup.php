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
        Schema::table('consat_calls', function (Blueprint $table) {
            $table->dropColumn('stop_point_id');
        });

        Schema::table('consat_call_details', function (Blueprint $table) {
            $table->dropColumn('event_type');
        });

        Schema::table('consat_passenger_count', function (Blueprint $table) {
            $table->dropPrimary(['date', 'id']);
            $table->dropColumn('id');
        });

        Schema::drop('consat_stops');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
