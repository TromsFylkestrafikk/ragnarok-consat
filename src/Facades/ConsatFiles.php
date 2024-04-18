<?php

namespace Ragnarok\Consat\Facades;

use Illuminate\Support\Facades\Facade;
use Ragnarok\Consat\Services\ConsatFiles as CFiles;

/**
 * @method static \Ragnarok\Sink\Models\SinkFile|null retrieveFile(string $dateStr)
 * @method static string filenameFromDate(string $dateStr)
 * @method static string|null dateFromFilename(string $filename)
 * @method static \Ragnarok\Sink\Services\RemoteFiles getRemote()
 * @method static \Illuminate\Contracts\Filesystem\Filesystem getRemoteDisk()
 * @method static void logPrintfInit(void $prefix = '', void ...$prefixArgs)
 *
 * @see Ragnarok\Consat\Services\ConsatFiles
 */
class ConsatFiles extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CFiles::class;
    }
}
