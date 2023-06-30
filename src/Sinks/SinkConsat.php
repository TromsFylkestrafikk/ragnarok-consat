<?php

namespace TromsFylkestrafikk\RagnarokConsat\Sinks;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use TromsFylkestrafikk\RagnarokSink\Sinks\SinkBase;

class SinkConsat extends SinkBase
{
    public $id = "consat";
    public $title = "Consat";

    /**
     * @inheritdoc
     */
    public function getFromDate(): Carbon
    {
        return new Carbon('2019-01-01');
    }

    /**
     * @inheritdoc
     */
    public function getToDate(): Carbon
    {
        return today()->subDay();
    }

    /**
     * @inheritdoc
     */
    public function fetch(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function import(): bool
    {
        Log::debug('Consat import. Yay!');
        return true;
    }
}
