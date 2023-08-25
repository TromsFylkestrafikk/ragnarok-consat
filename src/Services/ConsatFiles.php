<?php

namespace TromsFylkestrafikk\RagnarokConsat\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use TromsFylkestrafikk\RagnarokConsat\Models\Call;
use TromsFylkestrafikk\RagnarokConsat\Models\CallDetail;
use TromsFylkestrafikk\RagnarokConsat\Models\Destination;
use TromsFylkestrafikk\RagnarokConsat\Models\PassengerCount;
use TromsFylkestrafikk\RagnarokConsat\Models\PlannedJourney;
use TromsFylkestrafikk\RagnarokConsat\Models\Stop;
use TromsFylkestrafikk\RagnarokSink\Traits\LogPrintf;
use TromsFylkestrafikk\RagnarokSink\Services\RemoteFiles;
use TromsFylkestrafikk\RagnarokSink\Services\LocalFiles;
use Illuminate\Support\Carbon;

/**
 * Handle historic data files from Consat
 */
class ConsatFiles
{
    use LogPrintf;

    public const DATE_REGEX = "/^(?P<date>20\d{2}-\d{2}-\d{2})\.(?P<ext>7z|zip)$/";

    /**
     * @var RemoteFiles
     */
    public $remoteFile = null;

    /**
     * @var LocalFiles
     */
    public $localFile = null;

    protected $remoteDisk = null;

    /**
     * Timestamp of last re-connect to remote disk
     *
     * @var Carbon|null
     */
    protected $lastConnect = null;

    public function __construct()
    {
        $this->logPrintfInit('[ConsatService]: ');
        $this->remoteDisk = $this->buildRemoteDisk();
        $this->remoteFile = new RemoteFiles('consat', $this->remoteDisk);
        $this->localFile = $this->remoteFile->getLocal();
    }

    /**
     * Build file name used for daily dump.
     */
    public function filenameFromDate(string $dateStr): string
    {
        return $dateStr . '.7z';
    }

    /**
     * Extract date portion of file name.
     *
     * @param string $filename
     *
     * @return string|null
     */
    public function getDateFromFilename($filename)
    {
        $matches = [];
        $hit = preg_match(ConsatFiles::DATE_REGEX, $filename, $matches);
        if (!$hit) {
            return null;
        }
        return $matches['date'];
    }

    protected function buildRemoteDisk(): Filesystem
    {
        return app('filesystem')->build(config('ragnarok_consat.remote_disk'));
    }
}
