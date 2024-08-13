<?php

namespace Ragnarok\Consat\Models;

use Illuminate\Database\Eloquent\Model;

class Stop extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $table = 'consat_stops';
    protected $keyType = 'string';
}
