<?php

namespace TromsFylkestrafikk\RagnarokConsat\Sinks;

use Illuminate\Support\Facades\Log;
use TromsFylkestrafikk\RagnarokSink\Sinks\SinkBase;

class SinkConsat extends SinkBase
{
    public $name = "Consat";

    /**
     * Fetch raw, unprocessed data from sink to local storage.
     *
     * @return bool True on success.
     */
    public function fetch(): bool
    {
        return true;
    }

    /**
     * Import one chunk from sink.
     *
     * @return bool
     */
    public function import(): bool
    {
        Log::debug('Consat import. Yay!');
        return true;
    }
}
