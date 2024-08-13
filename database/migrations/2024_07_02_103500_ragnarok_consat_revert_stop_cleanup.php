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
        Schema::create('consat_stops', function (Blueprint $table) {
            $table->char('id')->comment('NSR/Regtopp quay ID');
            $table->date('date')->comment('Date for when stop ID is valid');
            $table->string('stop_name')->comment('Stop place name');
            $table->double('latitude')->comment('Latitude of stop place');
            $table->double('longitude')->comment('Longitude of stop place');

            $table->primary(['date', 'id']);
        });

        Schema::create('consat_invalidated_journeys', function (Blueprint $table) {
            $table->date('date')->comment('Date of the affected journey');
            $table->unsignedBigInteger('planned_journey_id')->comment('Reference to table consat_planned_journey');
            $table->dateTime('validity_start')->comment('Time is in local timezone');
            $table->dateTime('validity_end')->comment('Time is in local timezone');
            $table->string('creator')->comment('Cancellation registered by...');
            $table->string('description')->nullable();

            $table->primary(['date', 'planned_journey_id']);
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

        Schema::table('consat_calls', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->after('date')->comment('Call ID generated by Consat')->change();
            $table->dropColumn('stop_name');
            $table->dropColumn('pax_on_board');
            $table->dropColumn('pax_in');
            $table->dropColumn('pax_out');
            $table->dropColumn('pax_from_last_journey');
            $table->dropColumn('pax_valid');
            $table->renameColumn('call_valid', 'valid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consat_stops');
        Schema::dropIfExists('consat_invalidated_journeys');
        Schema::dropIfExists('consat_passenger_count');

        Schema::table('consat_calls', function (Blueprint $table) {
            $table->string('stop_name')->after('stop_quay_id')->comment('Name of stop');
            $table->integer('pax_on_board')->nullable()->comment('Pax already on board on this call');
            $table->smallInteger('pax_in')->nullable()->comment('Pax entering vehicle on this call');
            $table->smallInteger('pax_out')->nullable()->comment('Pax leaving vehicle on this call');
            $table->smallInteger('pax_from_last_journey')->nullable()->comment('Pax aggregated from last journey');
            $table->boolean('pax_valid')->nullable()->default(false)->comment('Pax count is valid. Filter on this for statistical purposes.');
            $table->renameColumn('valid', 'call_valid');
        });
    }
};