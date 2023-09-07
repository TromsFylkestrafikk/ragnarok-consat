<?php

namespace TromsFylkestrafikk\RagnarokConsat\Services;

use Illuminate\Support\Facades\DB;
use TromsFylkestrafikk\RagnarokConsat\Facades\ConsatFiles;

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
    public function import($dateStr)
    {
        $file = ConsatFiles::getLocal()->getFile(ConsatFiles::filenameFromDate($dateStr));
        $extractor = new ZipExtractor($file);
        $mapFactory = new ConsatMapper($extractor->getDisk());
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
    public function deleteImport($dateStr)
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
}
