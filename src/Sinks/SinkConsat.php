<?php

namespace Ragnarok\Consat\Sinks;

use Illuminate\Support\Carbon;
use Ragnarok\Consat\Facades\ConsatFiles;
use Ragnarok\Consat\Facades\ConsatImporter;
use Ragnarok\Sink\Sinks\SinkBase;
use Ragnarok\Sink\Traits\LogPrintf;

class SinkConsat extends SinkBase
{
    use LogPrintf;

    public static $id = 'consat';
    public static $title = "Consat";
    public $cron = '35 09 * * *';

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
        $file = ConsatFiles::retrieveFile($id);
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
        ConsatImporter::deleteImport($id)->import($id);
        return true;
    }

    public function deleteImport($id): bool
    {
        ConsatImporter::deleteImport($id);
        return true;
    }
}
