<?php

namespace TromsFylkestrafikk\RagnarokConsat\Sinks;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use TromsFylkestrafikk\RagnarokConsat\Services\ConsatFiles;
use TromsFylkestrafikk\RagnarokSink\Sinks\SinkBase;

class SinkConsat extends SinkBase
{
    public $id = "consat";
    public $title = "Consat";

    /**
     * @var ConsatFiles
     */
    protected $consat = null;

    public function __construct()
    {
        $this->consat = app(ConsatFiles::class);
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
    public function fetch($id): bool
    {
        try {
            $file = $this->consat->remoteFile->getFile($this->consat->filenameFromDate($id));
        } catch (Exception $except) {
            return false;
        }
        return $file ? true : false;
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
