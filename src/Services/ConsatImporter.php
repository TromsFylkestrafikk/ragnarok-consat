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
        'PlannedJourneys.csv' => \Ragnarok\Consat\Models\PlannedJourney::class,
        'StopPoint.csv' => \Ragnarok\Consat\Models\Stop::class,
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
        $extractor->extractContent();
        $mapFactory = new ConsatMapper($extractor->getDisk());
        $this->addMapperExceptions($mapFactory, $dateStr);
        foreach ($this->prioritizeCsvs($extractor->getFiles()) as $csvFile) {
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
            'consat_stops',
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
     * Sort csvs by priority.
     *
     * Manually override the order the CSVs are handed over to ConsatMapper.
     * This is useful to force some tables to be loaded before other in case
     * they depend on each other.
     */
    protected function prioritizeCsvs(array $csvs)
    {
        $order = [
            'StopPoint.csv' => 1,
            'PassengerCount.csv' => 2,
        ];
        usort($csvs, function ($alice, $bob) use ($order) {
            $aWeight = $order[basename($alice)] ?? 1000;
            $bWeight = $order[basename($bob)] ?? 1000;
            return $aWeight - $bWeight;
        });
        return $csvs;
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
