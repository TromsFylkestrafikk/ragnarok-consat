<?php

namespace Ragnarok\Consat\Sinks;

use Illuminate\Support\Carbon;
use Ragnarok\Consat\Facades\ConsatFiles;
use Ragnarok\Consat\Facades\ConsatImporter;
use Ragnarok\Sink\Models\SinkFile;
use Ragnarok\Sink\Sinks\SinkBase;

class SinkConsat extends SinkBase
{
    public static $id = 'consat';
    public static $title = "Consat";
    public $cron = '35 09 * * *';

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
    public function fetch(string $id): SinkFile|null
    {
        return ConsatFiles::retrieveFile($id);
    }

    /**
     * @inheritdoc
     */
    public function import(string $id, SinkFile $file): int
    {
        $importer = ConsatImporter::deleteImport($id)->import($id, $file);
        return $importer->getImportRecordCount();
    }

    public function deleteImport(string $id, SinkFile $file): bool
    {
        ConsatImporter::deleteImport($id);
        return true;
    }
}
