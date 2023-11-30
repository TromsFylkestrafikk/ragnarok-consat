<?php

namespace Ragnarok\Consat\Sinks;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;
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
    public function fetch(string $id): int
    {
        $file = ConsatFiles::retrieveFile($id);
        return $file ? $file->size : 0;
    }

    /**
     * @inheritdoc
     */
    public function getChunkVersion(string $id): string
    {
        return ConsatFiles::getChunkFile($id)->checksum;
    }

    /**
     * @inheritdoc
     */
    public function getChunkFiles(string $id): Collection
    {
        return ConsatFiles::getChunkFiles($id);
    }

    /**
     * @inheritdoc
     */
    public function removeChunk(string $id): bool
    {
        ConsatFiles::getLocal()->rmFile(ConsatFiles::filenameFromDate($id));
        return true;
    }

    /**
     * @inheritdoc
     */
    public function import(string $id): int
    {
        $importer = ConsatImporter::deleteImport($id)->import($id);
        return $importer->getImportRecordCount();
    }

    public function deleteImport(string $id): bool
    {
        ConsatImporter::deleteImport($id);
        return true;
    }
}
