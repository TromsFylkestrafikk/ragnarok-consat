<?php

namespace Ragnarok\Consat\Facades;

use Illuminate\Support\Facades\Facade;
use Ragnarok\Consat\Services\ConsatImporter as CImporter;

/**
 * @method static \Ragnarok\Consat\Services\ConsatImporter import(string $dateStr, \Ragnarok\Sink\Models\SinkFile $file)
 * @method static \Ragnarok\Consat\Services\ConsatImporter deleteImport(string $dateStr)
 * @method static int getImportRecordCount()
 * @method static string|null getCsvModel(string $csv)
 *
 * @see \Ragnarok\Consat\Services\ConsatImporter
 */
class ConsatImporter extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CImporter::class;
    }
}
