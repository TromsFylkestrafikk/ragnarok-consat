<?php

namespace Ragnarok\Consat\Facades;

use Illuminate\Support\Facades\Facade;
use Ragnarok\Consat\Services\ConsatImporter as CImporter;

/**
 * @method static \Ragnarok\Consat\Services\ConsatImporter import(string $dateStr)
 * @method static \Ragnarok\Consat\Services\ConsatImporter deleteImport(string $dateStr)
 */
class ConsatImporter extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CImporter::class;
    }
}
