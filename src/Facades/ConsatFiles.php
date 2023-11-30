<?php

namespace Ragnarok\Consat\Facades;

use Illuminate\Support\Facades\Facade;
use Ragnarok\Consat\Services\ConsatFiles as CFiles;

/**
 * @method static \Ragnarok\Sink\Models\RawFile retrieveFile(string $dateStr)
 * @method static string filenameFromDate(string $dateStr)
 * @method static string|null getDateFromFilename(string $filename)
 * @method static \Ragnarok\Sink\Models\RawFile getChunkFile($dateStr)
 * @method static \Illuminate\Database\Eloquent\Collection getChunkFiles($dateStr)
 * @method static \Ragnarok\Sink\Services\RemoteFiles getRemote()
 * @method static \Ragnarok\Sink\Services\LocalFiles getLocal()
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
