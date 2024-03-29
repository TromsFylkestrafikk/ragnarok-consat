<?php

namespace Ragnarok\Consat\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Ragnarok\Sink\Models\SinkFile;

/**
 * Handle logic around importing zip archives to database.
 */
class ConsatImporter
{
    protected $importRecordCount = 0;

    protected $csvModelMap = [
        'CallDetails.csv' => \Ragnarok\Consat\Models\CallDetail::class,
        'Calls.csv' => \Ragnarok\Consat\Models\Call::class,
        'PassengerCount.csv' => \Ragnarok\Consat\Models\PassengerCount::class,
        'PlannedJourneys.csv' => \Ragnarok\Consat\Models\PlannedJourney::class,
        'StopPoint.csv' => \Ragnarok\Consat\Models\Stop::class,
        'Destination.csv' => \Ragnarok\Consat\Models\Destination::class,
    ];

    /**
     * @param string $dateStr Date in 'yyyy-mm-dd' format.
     *
     * @return $this
     */
    public function import(string $dateStr, SinkFile $file): ConsatImporter
    {
        $this->importRecordCount = 0;
        $extractor = new ZipExtractor($file);
        $mapFactory = new ConsatMapper($extractor->getDisk());
        $this->addMapperExceptions($mapFactory, $dateStr);
        foreach ($extractor->getFiles() as $csvFile) {
            $mapper = $mapFactory->getMapper($csvFile);
            if (!$mapper) {
                continue;
            }
            $this->importRecordCount += $mapper->exec()->logSummary()->getProcessedRecords();
        }
        $extractor->cleanUp();
        return $this;
    }

    /**
     * @param string $dateStr
     *
     * @return $this
     */
    public function deleteImport($dateStr): ConsatImporter
    {
        $tables = [
            'consat_planned_journeys',
            'consat_calls',
            'consat_call_details',
            'consat_passenger_count',
            'consat_stops',
            'consat_destinations'
        ];

        foreach ($tables as $table) {
            DB::table($table)->where('date', $dateStr)->delete();
        }
        return $this;
    }

    public function getImportRecordCount(): int
    {
        return $this->importRecordCount;
    }

    /**
     * Get eloquent model used for given csv file.
     */
    public function getCsvModel(string $csv): string|null
    {
        return $this->csvModelMap[$csv] ?? null;
    }

    /**
     * Set what csv files to not import for given date.
     */
    protected function addMapperExceptions(ConsatMapper $mapFactory, string $dateStr): ConsatImporter
    {
        $today = today();
        foreach (config('ragnarok_consat.max_age', []) as $csv => $period) {
            if ((new Carbon($dateStr))->add($period)->isBefore($today)) {
                $mapFactory->except($csv);
            }
        }
        return $this;
    }
}
