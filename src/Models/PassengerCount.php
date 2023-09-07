<?php

namespace TromsFylkestrafikk\RagnarokConsat\Models;

use Illuminate\Database\Eloquent\Model;

class PassengerCount extends Model
{
    public $timestamps = false;
    protected $table = 'consat_passenger_count';
}
