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
    public function destinationTables(): array
    {
        return [
            'consat_planned_journeys' => 'All journeys for a given day',
            'consat_invalidated_journeys' => 'All cancelled journeys for a given day',
            'consat_calls' => 'All calls at all stops for all journeys',
            'consat_call_details' => 'All vehicle events. Grouped by call. Due to its massive size, old data will be deleted',
            'consat_passenger_count' => 'Automatic Passenger Counted data. Aggregated per call.',
            'consat_stops' => 'All stops involved in route set per day',
        ];
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

    /**
     * @inheritdoc
     */
    public function deleteImport(string $id, SinkFile $file): bool
    {
        ConsatImporter::deleteImport($id);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function filenameToChunkId(string $filename): string
    {
        return ConsatFiles::dateFromFilename($filename);
    }
}
