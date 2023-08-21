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
use Illuminate\Support\Carbon;

/**
 * Services around historic route and real time data from Consat.
 */
class ConsatHistoric
{
    use LogPrintf;

    public const DATE_REGEX = "/^(?P<date>20\d{2}-\d{2}-\d{2})\.(?P<ext>7z|zip)$/";

    /**
     * @var RemoteFiles
     */
    public $remoteFile = null;

    protected $remoteDisk = null;

    protected $dataModels = [
        Call::class,
        CallDetail::class,
        Destination::class,
        PassengerCount::class,
        PlannedJourney::class,
        Stop::class,
    ];

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
        $this->remoteFile = new RemoteFiles('Consat', $this->remoteDisk);
    }

    /**
     * Remove ancient or superfluous data.
     *
     * @return $this
     */
    public function cleanupAncient($months = 3): ConsatHistoric
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
        $hit = preg_match(ConsatHistoric::DATE_REGEX, $filename, $matches);
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
