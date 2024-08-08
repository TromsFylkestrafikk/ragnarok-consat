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
            $table->date('date')->comment('Date for when stop ID is valid');
            $table->unsignedBigInteger('id')->comment('Consat internal ID for this stop');
            $table->string('stop_quay_id')->comment('NSR quay ID (or Regtopp for old sets)');
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consat_stops');
        Schema::dropIfExists('consat_invalidated_journeys');
    }
};
