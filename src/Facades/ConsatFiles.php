<?php

namespace Ragnarok\Consat\Facades;

use Illuminate\Support\Facades\Facade;
use Ragnarok\Consat\Services\ConsatFiles as CFiles;

/**
 * @method static \Ragnarok\Sink\Models\SinkFile retrieveFile(string $dateStr)
 * @method static string filenameFromDate(string $dateStr)
 * @method static string dateFromFilename(string $filename)
 * @method static \Ragnarok\Sink\Services\RemoteFiles getRemote()
 * @method static \Illuminate\Contracts\Filesystem\Filesystem getRemoteDisk()
 */
class ConsatFiles extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CFiles::class;
    }
}
