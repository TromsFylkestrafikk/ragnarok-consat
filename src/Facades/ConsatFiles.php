<?php

namespace TromsFylkestrafikk\RagnarokConsat\Facades;

use Illuminate\Support\Facades\Facade;
use TromsFylkestrafikk\RagnarokConsat\Services\ConsatFiles as CFiles;

/**
 * @method static \TromsFylkestrafikk\RagnarokSink\Models\RawFile retrieveFile(string $dateStr)
 * @method static string filenameFromDate(string $dateStr)
 * @method static string|null getDateFromFilename(string $filename)
 * @method static \TromsFylkestrafikk\RagnarokSink\Services\RemoteFiles getRemote()
 * @method static \TromsFylkestrafikk\RagnarokSink\Services\LocalFiles getLocal()
 * @method static \Illuminate\Contracts\Filesystem\Filesystem getLocalDisk()
 * @method static \Illuminate\Contracts\Filesystem\Filesystem getRemoteDisk()
 */
class ConsatFiles extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CFiles::class;
    }
}
