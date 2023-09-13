<?php

namespace Ragnarok\Consat\Services;

use Archive7z\Archive7z;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Filesystem\Filesystem;
use Ragnarok\Consat\Facades\ConsatFiles;
use Ragnarok\Sink\Models\RawFile;

class ZipExtractor
{
    /**
     * @var string|null
     */
    protected $outputDir = null;

    /**
     * @var Filesystem
     */
    protected $tmpDisk;

    public function __construct(protected RawFile $zipFile)
    {
        //
    }

    public function __destruct()
    {
        if ($this->outputDir) {
            $this->cleanUp();
        }
    }

    /**
     * @return string  Directory relative to 'tmp' disk where extracted.
     */
    public function extractContent()
    {
        $zFilepath = ConsatFiles::getLocalDisk()->path($this->zipFile->name);

        $filebase = basename($this->zipFile->name, '.7z');
        $this->outputDir = uniqid("consat-{$filebase}-");
        $this->getDisk()->makeDirectory($this->outputDir);
        $archive = new Archive7z($zFilepath);
        $archive->setOutputDirectory($this->getDisk()->path($this->outputDir))->extract();
        return $this->outputDir;
    }

    /**
     * Get all files in archive.
     *
     * Note: Files here are relative to the filesystem/disk used by
     * $this->getDisk()
     *
     * @return array
     */
    public function getFiles()
    {
        if (!$this->getOutputDir()) {
            $this->extractContent();
        }
        return $this->getDisk()->files($this->getOutputDir());
    }

    /**
     * @return Filesystem where zip is extracted to.
     */
    public function getDisk()
    {
        if (!$this->tmpDisk) {
            $this->tmpDisk = Storage::disk(config('ragnarok_consat.tmp_disk'));
        }
        return $this->tmpDisk;
    }

    /**
     * @return string
     */
    public function getOutputDir()
    {
        return $this->outputDir;
    }

    /**
     * @return string
     */
    public function getFullOutputDir()
    {
        return $this->getDisk()->path($this->getOutputDir());
    }

    /**
     * @return bool
     */
    public function cleanUp()
    {
        return $this->getDisk()->deleteDirectory($this->getOutputDir());
    }
}
