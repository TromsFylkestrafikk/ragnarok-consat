<?php

namespace TromsFylkestrafikk\RagnarokConsat\Models;

use Illuminate\Database\Eloquent\Model;

class PlannedJourney extends Model
{
    public $timestamps = false;
    protected $table = 'consat_planned_journeys';
}
