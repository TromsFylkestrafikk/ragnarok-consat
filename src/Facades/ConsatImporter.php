<?php

namespace TromsFylkestrafikk\RagnarokConsat\Facades;

use Illuminate\Support\Facades\Facade;
use TromsFylkestrafikk\RagnarokConsat\Services\ConsatImporter as CImporter;

/**
 * @method static \TromsFylkestrafikk\RagnarokConsat\Services\ConsatImporter import(string $dateStr)
 * @method static \TromsFylkestrafikk\RagnarokConsat\Services\ConsatImporter deleteImport(string $dateStr)
 */
class ConsatImporter extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CImporter::class;
    }
}
