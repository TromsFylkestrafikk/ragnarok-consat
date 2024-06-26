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
            $table->integer('pax_on_board')->nullable()->comment('Pax already on board on this call');
            $table->smallInteger('pax_in')->nullable()->comment('Pax entering vehicle on this call');
            $table->smallInteger('pax_out')->nullable()->comment('Pax leaving vehicle on this call');
            $table->smallInteger('pax_from_last_journey')->nullable()->comment('Pax aggregated from last journey');
            $table->boolean('pax_valid')->nullable()->default(false)->comment('Pax count is valid. Filter on this for statistical purposes.');
            $table->renameColumn('nsr_quay_id', 'stop_quay_id');
            $table->renameColumn('valid', 'call_valid');
            $table->dropColumn('stop_point_id');
        });

        Schema::table('consat_call_details', function (Blueprint $table) {
            $table->dropColumn('event_type');
        });

        Schema::table('consat_planned_journeys', function (Blueprint $table) {
            $table->dateTime('journey_start')->nullable()->comment('Planned start time of journey. Time is in local timezone');
            $table->dateTime('journey_end')->nullable()->comment('Planned end time of journey. Time is in local timezone');
            $table->char('direction')->nullable()->comment('Direction code of journey. Inbound or Outbound.');
        });

        Schema::drop('consat_destinations');
        Schema::drop('consat_passenger_count');
        Schema::drop('consat_stops');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consat_calls', function (Blueprint $table) {
            $table->dropColumn('pax_on_board');
            $table->dropColumn('pax_in');
            $table->dropColumn('pax_out');
            $table->dropColumn('pax_from_last_journey');
            $table->dropColumn('pax_valid');
            $table->renameColumn('call_valid', 'valid');
            $table->renameColumn('stop_quay_id', 'nsr_quay_id');
            $table->unsignedBigInteger('stop_point_id')->nullable()->after('sequence')->comment('References `consat_stops.id`');
        });

        Schema::table('consat_call_details', function (Blueprint $table) {
            $table->unsignedTinyInteger('event_type')->nullable()->after('call_id')->comment('Event type ID');
        });

        Schema::table('consat_planned_journeys', function (Blueprint $table) {
            $table->dropColumn('journey_start');
            $table->dropColumn('journey_end');
            $table->dropColumn('direction');
        });

        Schema::create('consat_stops', function (Blueprint $table) {
            $table->date('date')->comment('Date for when stop ID is valid');
            $table->unsignedBigInteger('id')->comment('Consat internal ID for this stop');
            $table->char('external_id', 64)->comment('NSR stop ID (or Regtopp for old sets)');
            $table->string('name', 128)->comment('Stop place name');
            $table->double('latitude')->comment('Latitude of stop place');
            $table->double('longitude')->comment('Longitude of stop place');

            $table->primary(['date', 'id']);
        });

        Schema::create('consat_destinations', function (Blueprint $table) {
            $table->date('date')->comment('Date for destination. Required join with other tables');
            $table->unsignedBigInteger('id')->comment('Consat internal ID.');
            $table->char('external_id', 64)->comment('NeTEx variant of destination ID (Or Regtopp for old sets)');
            $table->string('destination', 128)->comment('Name of destination');

            $table->primary(['date', 'id']);
        });

        Schema::create('consat_passenger_count', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->comment('ID generated by Consat');
            $table->date('date')->comment('Date of data set. Required join with other tables');
            $table->timestamp('timestamp')->comment('Timestamp of pax count. Date + Time');
            $table->unsignedBigInteger('call_id')->comment('Call ID this pax state is connected to. References `consat_calls.id`');
            $table->integer('on_board')->default(0)->comment('Pax already on board on this call');
            $table->smallInteger('in')->default(0)->comment('Pax entering vehicle on this call');
            $table->smallInteger('out')->default(0)->comment('Pax leaving vehicle on this call');
            $table->smallInteger('from_last_journey')->nullable()->comment('Pax aggregated from last journey');
            $table->boolean('valid')->nullable()->default(false)->comment('Pax count is valid. Filter on this for statistical purposes.');

            $table->primary(['date', 'id']);
            $table->index(['date', 'call_id']);
        });
    }
};
