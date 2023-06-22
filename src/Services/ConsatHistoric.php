<?php

namespace TromsFylkestrafikk\RagnarokConsat\Services;

use TromsFylkestrafikk\RagnarokConsat\Models\Call;
use TromsFylkestrafikk\RagnarokConsat\Models\CallDetail;
use TromsFylkestrafikk\RagnarokConsat\Models\Destination;
use TromsFylkestrafikk\RagnarokConsat\Models\RawFile;
use TromsFylkestrafikk\RagnarokConsat\Models\PassengerCount;
use TromsFylkestrafikk\RagnarokConsat\Models\PlannedJourney;
use TromsFylkestrafikk\RagnarokConsat\Models\Stop;
use TromsFylkestrafikk\RagnarokSink\Traits\LogPrintf;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Filesystem\Filesystem;

/**
 * Services around historic route and real time data from Consat.
 */
class ConsatHistoric
{
    use LogPrintf;

    public const DATE_REGEX = "/^(?P<date>20\d{2}-\d{2}-\d{2})\.(?P<ext>7z|zip)$/";

    protected $dataModels = [
        Call::class,
        CallDetail::class,
        Destination::class,
        PassengerCount::class,
        PlannedJourney::class,
        Stop::class,
    ];

    /**
     * @var Filesystem
     */
    protected $rDisk = null;

    /**
     * @var Filesystem
     */
    protected $lDisk = null;

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
     * Get zip file model for given date.
     *
     * @param string $date
     *
     * @return RawFile|null
     */
    public function getZipFile($date)
    {
        /** @var RawFile $file */
        $file = RawFile::where('date', $date)->first();
        if (!$file) {
            $file = $this->createZipFile($date);
        } elseif (!$this->getLocalDisk()->exists($file->name)) {
            $file = $this->refreshZipFile($file);
        }
        return $file;
    }

    /**
     * Check existence and checksum for given local file.
     *
     * @return bool
     */
    public function localChecksOut(RawFile $file)
    {
        $lDisk = $this->getLocalDisk();
        return $lDisk->exists($file->name) && md5($lDisk->get($file->name)) === $file->checksum;
    }

    /**
     * Compare local file with remote, download and update status.
     *
     * @param RawFile $file
     *
     * @return RawFile
     */
    public function refreshZipFile(RawFile $file)
    {
        $newContent = $this->getRemoteFileContent($file->name);
        $existsLocal = $this->getLocalDisk()->exists($file->name);
        if (!$newContent) {
            // Server might be down. Not touching state of file unless the local
            // file is missing.
            if (!$existsLocal) {
                throw new Exception("Missing both local and remote file. Consider 'consat:purge'");
            }
            return $file;
        }
        $newChecksum = md5($newContent);
        if ($newChecksum !== $file->checksum || !$existsLocal) {
            $this->getLocalDisk()->put($file->name, $newContent);
            if ($newChecksum !== $file->checksum) {
                $file->checksum = $newChecksum;
                $file->import_status = 'updated';
                $file->save();
            }
        }
        return $file;
    }

    /**
     * Purge all historic data for a given day
     *
     * @param string $date
     */
    public function purgeDay($date)
    {
        foreach ($this->dataModels as $modelName) {
            $modelName::where('date', $date)->delete();
        }
        $file = RawFile::whereDate('date', $date)->first();
        if (!$file) {
            return;
        }
        $file->import_status = 'new';
        $file->save();
    }

    /**
     * Purge the copied zip file for a given day
     *
     * @param string $date
     */
    public function purgeZipFile($date)
    {
        $this->purgeDay($date);
        $file = RawFile::where('date', $date)->first();
        if (!$file) {
            return;
        }
        $lDisk = $this->getLocalDisk();
        if ($lDisk->exists($file->name)) {
            $lDisk->delete($file->name);
        }
        $file->delete();
    }

    /**
     * Remove ancient or superfluous data.
     *
     * @return $this
     */
    public function cleanupAncient($months = 3)
    {
        $this->info("BEGIN cleanup of ancient data ...");
        // Stub.
        //
        // Call details consumes 88% of all data from consat. Consider this
        // fresh-ware and remove stuff older than three months.
        CallDetail::whereDate('date', '<', (new Carbon())->subMonths($months))->delete();
        $this->info("END cleanup of ancient data");
        return $this;
    }

    /**
     * @return Filesystem
     */
    public function getRemoteDisk()
    {
        if (!$this->rDisk) {
            $this->reConnect();
        } else {
            $timeout = (new Carbon())->subSeconds(config('filesystems.consat_historic_remote.timeout'));
            if ($this->lastConnect->isAfter($timeout)) {
                $this->reConnect();
            }
        }
        return $this->rDisk;
    }

    /**
     * @return Filesystem
     */
    public function getLocalDisk()
    {
        if (!$this->lDisk) {
            $this->lDisk = Storage::disk('consat_historic_local');
        }
        return $this->lDisk;
    }

    /**
     * List remote files.
     *
     * @return array
     */
    public function listRemote()
    {
        return $this->fileFilter($this->getRemoteDisk()->allFiles());
    }

    /**
     * List local list of files.
     *
     * @return array
     */
    public function listLocal()
    {
        return $this->fileFilter($this->getLocalDisk()->allFiles());
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
        $hit = preg_match(ConsatHistoric::DATE_REGEX, $filename, $matches);
        if (!$hit) {
            return null;
        }
        return $matches['date'];
    }

    /**
     * Create a new zip file model for given date.
     *
     * @return RawFile|null
     */
    protected function createZipFile($date)
    {
        $filename = $date . ".7z";
        $copied = $this->copyFile($filename);
        if (!$copied) {
            return null;
        }

        /** @var RawFile $file */
        $file = RawFile::create([
            'name' => $filename,
            'checksum' => md5($this->getLocalDisk()->get($filename)),
            'date' => $date,
            'import_status' => 'new',
            'import_msg' => null,
        ]);
        return $file;
    }

    /**
     * @param RawFile $zipFile
     *
     * @return RawFile
     */
    protected function updateZipFile($zipFile)
    {
        $copyOk = $this->copyFile($zipFile->name);
        if (!$copyOk) {
            $zipFile->import_status = 'empty';
            $zipFile->checksum = md5(random_bytes(256));
        } else {
            $zipFile->import_status = 'updated';
            $zipFile->checksum = md5($this->getLocalDisk()->get($zipFile->name));
        }
        $zipFile->save();
        return $zipFile;
    }

    /**
     * Copies file from remote to local
     *
     * @param string $filename
     *
     * @return bool True if success
     */
    protected function copyFile($filename)
    {
        $content = $this->getRemoteFileContent($filename);
        if (!$content) {
            return false;
        }
        return $this->getLocalDisk()->put($filename, $content);
    }

    protected function getRemoteFileContent($filename)
    {
        $rDisk = $this->getRemoteDisk();
        if (!$rDisk->exists($filename)) {
            return null;
        }
        return $rDisk->get($filename);
    }

    /**
     * @return $this
     */
    protected function reConnect()
    {
        $this->rDisk = Storage::disk('consat_historic_remote');
        $this->lastConnect = new Carbon();
        return $this;
    }

    protected function fileFilter($filenames)
    {
        return array_filter($filenames, function ($filename) {
            return preg_match(self::DATE_REGEX, $filename);
        });
    }
}
