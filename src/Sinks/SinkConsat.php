<?php

namespace Ragnarok\Consat\Sinks;

use Exception;
use Illuminate\Support\Carbon;
use Ragnarok\Consat\Facades\ConsatFiles;
use Ragnarok\Consat\Facades\ConsatImporter;
use Ragnarok\Sink\Sinks\SinkBase;
use Ragnarok\Sink\Traits\LogPrintf;

class SinkConsat extends SinkBase
{
    use LogPrintf;

    public $id = "consat";
    public $title = "Consat";

    public function __construct()
    {
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
            $file = ConsatFiles::retrieveFile($id);
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
        ConsatFiles::getLocal()->rmFile(ConsatFiles::filenameFromDate($id));
        return true;
    }

    /**
     * @inheritdoc
     */
    public function import($id): bool
    {
        try {
            ConsatImporter::deleteImport($id)->import($id);
        } catch (Exception $except) {
            $this->error($this->exceptionToStr($except));
            return false;
        }
        return true;
    }

    public function deleteImport($id): bool
    {
        ConsatImporter::deleteImport($id);
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
