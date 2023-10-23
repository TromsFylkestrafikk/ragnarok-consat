<?php

namespace Ragnarok\Consat\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Ragnarok\Consat\Facades\ConsatFiles;

/**
 * Handle logic around importing zip archives to database.
 */
class ConsatImporter
{
    /**
     * @param string $dateStr Date in 'yyyy-mm-dd' format.
     *
     * @return $this
     */
    public function import($dateStr): ConsatImporter
    {
        $file = ConsatFiles::getLocal()->getFile(ConsatFiles::filenameFromDate($dateStr));
        $extractor = new ZipExtractor($file);
        $mapFactory = new ConsatMapper($extractor->getDisk());
        $this->addMapperExceptions($mapFactory, $dateStr);
        foreach ($extractor->getFiles() as $csvFile) {
            $mapper = $mapFactory->getMapper($csvFile);
            if (!$mapper) {
                continue;
            }
            $mapper->exec()->logSummary();
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
