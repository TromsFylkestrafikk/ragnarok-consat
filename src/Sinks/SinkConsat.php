<?php

namespace TromsFylkestrafikk\RagnarokConsat\Sinks;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use TromsFylkestrafikk\RagnarokConsat\Services\ConsatHistoric;
use TromsFylkestrafikk\RagnarokSink\Sinks\SinkBase;

class SinkConsat extends SinkBase
{
    public $id = "consat";
    public $title = "Consat";

    /**
     * @var ConsatHistoric
     */
    protected $consat = null;

    public function __construct()
    {
        $this->consat = app(ConsatHistoric::class);
    }

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
    public function fetch($ids = []): bool
    {
        foreach ($ids as $date) {
            $this->consat->remoteFile->getFile($this->consat->filenameFromDate($date));
        }
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
