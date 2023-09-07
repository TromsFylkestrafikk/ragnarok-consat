<?php

namespace TromsFylkestrafikk\RagnarokConsat\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use TromsFylkestrafikk\RagnarokSink\Models\RawFile;
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
    protected $remoteFile = null;

    /**
     * @var LocalFiles
     */
    protected $localFile = null;

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
     * @return RawFile|null
     */
    public function retrieveFile($dateStr)
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
     * Extract date portion of file name.
     *
     * @param string $filename
     *
     * @return string|null
     */
    public function getDateFromFilename($filename)
    {
        $matches = [];
        $hit = preg_match(self::DATE_REGEX, $filename, $matches);
        if (!$hit) {
            return null;
        }
        return $matches['date'];
    }

    public function getLocal(): LocalFiles
    {
        if ($this->localFile === null) {
            $this->localFile = $this->getRemote()->getLocal();
        }
        return $this->localFile;
    }

    public function getRemote(): RemoteFiles
    {
        if ($this->remoteFile === null) {
            $this->remoteFile = new RemoteFiles('consat', $this->remoteDisk);
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

    public function getLocalDisk(): Filesystem
    {
        return $this->getLocal()->getDisk();
    }
}
