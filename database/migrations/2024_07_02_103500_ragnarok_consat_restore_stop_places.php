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
            $table->char('stop_quay_id', 64)->comment('NSR quay ID (or Regtopp for old sets)');
            $table->string('stop_name', 128)->comment('Stop place name');
            $table->double('latitude')->comment('Latitude of stop place');
            $table->double('longitude')->comment('Longitude of stop place');

            $table->primary(['date', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consat_stops');
    }
};
