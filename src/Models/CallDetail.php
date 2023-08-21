<?php

namespace TromsFylkestrafikk\RagnarokConsat\Models;

use Illuminate\Database\Eloquent\Model;

class CallDetail extends Model
{
    public $timestamps = false;
    protected $table = 'consat_historic_call_details';
}
