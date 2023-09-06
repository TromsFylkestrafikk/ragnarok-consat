<?php

namespace TromsFylkestrafikk\RagnarokConsat\Sinks;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use TromsFylkestrafikk\RagnarokConsat\Services\ConsatFiles;
use TromsFylkestrafikk\RagnarokConsat\Services\ConsatImporter;
use TromsFylkestrafikk\RagnarokSink\Sinks\SinkBase;
use TromsFylkestrafikk\RagnarokSink\Traits\LogPrintf;

class SinkConsat extends SinkBase
{
    use LogPrintf;

    public $id = "consat";
    public $title = "Consat";

    /**
     * @var ConsatFiles
     */
    protected $consat = null;

    public function __construct()
    {
        $this->consat = app(ConsatFiles::class);
        $this->logPrintfInit('[SinkConsat]: ');
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
            $file = $this->consat->retrieveFile($id);
        } catch (Exception $except) {
            return false;
        }
        return $file ? true : false;
    }

    /**
     * @inheritdoc
     */
    public function removeChunk($id): bool
    {
        $this->consat->localFile->rmFile($this->consat->filenameFromDate($id));
        return true;
    }

    /**
     * @inheritdoc
     */
    public function import($id): bool
    {
        $importer = new ConsatImporter();
        try {
            $importer->import($id);
        } catch (Exception $except) {
            $this->error($this->exceptionToStr($except));
            return false;
        }
        return true;
    }

    public function deleteImport($id): bool
    {
        Log::debug('Consat import delete. Booo!');
        return true;
    }

    /**
     * @param Exception $except
     *
     * @return string
     */
    protected function exceptionToStr(Exception $except)
    {
        return sprintf(
            "%s(%d): %s\n%s",
            $except->getFile(),
            $except->getLine(),
            $except->getMessage(),
            $except->getTraceAsString()
        );
    }
}
