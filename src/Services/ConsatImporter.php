<?php

namespace TromsFylkestrafikk\RagnarokConsat\Services;

use TromsFylkestrafikk\RagnarokSink\Models\RawFile;

/**
 * Handle logic around importing zip archives to database.
 */
class ConsatImporter
{
    /**
     * @var ConsatFiles
     */
    protected $consat;

    public function __construct()
    {
        $this->consat = app(ConsatFiles::class);
    }

    public function import($dateStr)
    {
        $file = $this->consat->localFile->getFile($this->consat->filenameFromDate($dateStr));
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
        return true;
    }
}
