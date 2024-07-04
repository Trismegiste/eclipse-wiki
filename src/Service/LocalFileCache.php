<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

use SplFileInfo;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * A generic local cache for files
 */
class LocalFileCache implements CacheWarmerInterface, CacheClearerInterface
{

    public function __construct(protected Filesystem $fs, protected string $folder)
    {
        
    }

    public function clear(string $cacheDir): void
    {
        if (is_dir($this->folder)) {
            $iter = new Finder();
            $iter->in($this->folder)->files();

            $this->fs->remove($iter);
        }
    }

    public function isOptional(): bool
    {
        return false;
    }

    public function warmUp(string $cacheDir): array
    {
        $this->fs->mkdir($this->folder);
        if (!is_readable($this->folder)) {
            throw new InvalidConfigurationException($this->folder . ' is not readable after its creation');
        }

        return [];
    }

    public function createResponse(string $filename): BinaryFileResponse
    {
        return new BinaryFileResponse($this->getFileInfo($filename));
    }

    public function getFileInfo(string $filename): SplFileInfo
    {
        $path = $this->folder . '/' . $filename;
        if (!file_exists($path)) {
            throw new NotFoundHttpException($filename . ' does not exist');
        }

        return new SplFileInfo($path);
    }

    public function getListing(): iterable
    {
        return (new Finder())
                        ->in($this->folder)
                        ->files()
                        ->sortByModifiedTime()
                        ->getIterator();
    }

    public function createTargetFile(string $filename): SplFileInfo
    {
        return new SplFileInfo($this->folder . '/' . $this->sanitizeFilename($filename));
    }

    protected function sanitizeFilename(string $filename): string
    {
        return preg_replace('#([^A-Za-z0-9-_\.])#', '-', iconv('UTF-8', 'ASCII//TRANSLIT', $filename));
    }

}
