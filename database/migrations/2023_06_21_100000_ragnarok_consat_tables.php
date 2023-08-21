<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RagnarokConsatTables extends Migration
{
    public function up(): void
    {
        Schema::create('consat_planned_journeys', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->date('date');
            $table->char('journey_id', 128);
            $table->char('line_id', 128);
            $table->unsignedSmallInteger('line');
            $table->unsignedSmallInteger('trip');
            $table->unsignedSmallInteger('company');

            $table->index(['date','journey_id']);
        });

        Schema::create('consat_calls', function (Blueprint $table) {
            $table->date('date');
            $table->unsignedInteger('id');
            $table->unsignedInteger('planned_journey_id');
            $table->smallInteger('sequence')->nullable();
            $table->unsignedInteger('stop_point_id')->nullable();
            $table->smallInteger('stop_duration')->nullable()->comment('Stop duration in seconds');
            $table->datetime('planned_arrival')->nullable();
            $table->datetime('planned_departure')->nullable();
            $table->datetime('actual_arrival')->nullable();
            $table->datetime('actual_departure')->nullable();
            $table->smallInteger('distance_next_point')->nullable()->comment('Measured distance to next point in journey');
            $table->integer('delay')->nullable();
            $table->char('vehicle', 64)->nullable();
            $table->boolean('valid')->default(false);

            $table->primary(['date', 'id']);
            $table->index(['date', 'planned_journey_id']);
        });

        Schema::create('consat_call_details', function (Blueprint $table) {
            $table->date('date');
            $table->timestamp('timestamp', 3);
            $table->unsignedInteger('call_id');
            $table->unsignedTinyInteger('event_type')->nullable();
            $table->unsignedInteger('distance')->nullable();
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();

            $table->primary(['call_id', 'timestamp']);
            $table->index(['date', 'call_id']);
        });

        Schema::create('consat_passenger_count', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->date('date');
            $table->timestamp('timestamp');
            $table->unsignedInteger('call_id');
            $table->unsignedInteger('on_board')->default(0);
            $table->unsignedSmallInteger('in')->default(0);
            $table->unsignedSmallInteger('out')->default(0);
            $table->unsignedSmallInteger('from_last_journey')->nullable();
            $table->boolean('valid')->default(false);

            $table->index(['date', 'call_id']);
        });

        Schema::create('consat_stops', function (Blueprint $table) {
            $table->date('date');
            $table->unsignedBigInteger('id');
            $table->char('external_id', 64);
            $table->string('name', 128);
            $table->double('latitude');
            $table->double('longitude');

            $table->primary(['date', 'id']);
        });

        Schema::create('consat_destinations', function (Blueprint $table) {
            $table->date('date');
            $table->unsignedBigInteger('id');
            $table->char('external_id', 64);
            $table->string('destination', 128);

            $table->primary(['date', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('consat_calls');
        Schema::dropIfExists('consat_call_details');
        Schema::dropIfExists('consat_planned_journeys');
        Schema::dropIfExists('consat_passenger_count');
        Schema::dropIfExists('consat_stops');
        Schema::dropIfExists('consat_destinations');
    }
}
