<?php

namespace Ragnarok\Consat\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Ragnarok\Sink\Models\SinkFile;
use Ragnarok\Sink\Traits\LogPrintf;
use Ragnarok\Sink\Services\RemoteFiles;
use Ragnarok\Consat\Sinks\SinkConsat;
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
    protected $remoteFile = null;

    /**
     * @var Filesystem
     */
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
    }

    /**
     * @param string $dateStr Date to get zip file for.
     *
     * @return SinkFile|null
     */
    public function retrieveFile(string $dateStr): SinkFile|null
    {
        return $this->getRemote()->getFile($this->filenameFromDate($dateStr));
    }

    /**
     * Build file name used for daily dump.
     */
    public function filenameFromDate(string $dateStr): string
    {
        return $dateStr . '.7z';
    }

    /**
     * Given a chunk file name, get the date from it.
     *
     * It's expected to match the pattern YYYY-MM-DD.7z
     */
    public function dateFromFilename(string $filename): string|null
    {
        $matches = [];
        $hits = preg_match('|(?P<date>\d{4}-\d{2}-\d{2})\.7z$|', $filename, $matches);
        return $hits ? $matches['date'] : null;
    }

    public function getRemote(): RemoteFiles
    {
        if ($this->remoteFile === null) {
            $this->remoteFile = new RemoteFiles(SinkConsat::$id, $this->getRemoteDisk());
        }
        return $this->remoteFile;
    }

    public function getRemoteDisk(): Filesystem
    {
        if ($this->remoteDisk === null) {
            $this->remoteDisk = app('filesystem')->build(config('ragnarok_consat.remote_disk'));
        }
        return $this->remoteDisk;
    }
}
