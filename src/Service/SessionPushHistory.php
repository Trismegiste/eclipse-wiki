<?php

/*
 * Eclipse Wiki
 */

namespace App\Service;

use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use function join_paths;

/**
 * History of Mercure push
 */
class SessionPushHistory implements CacheWarmerInterface, CacheClearerInterface
{

    const subDir = 'push_history';

    protected string $cacheDir;

    public function __construct(protected Filesystem $fs, string $cacheDir)
    {
        $this->cacheDir = join_paths($cacheDir, self::subDir);
    }

    public function clear(string $cacheDir): void
    {
        $folder = join_paths($cacheDir, self::subDir);
        if (is_dir($folder)) {
            $iter = new Finder();
            $iter->in($folder)->files();

            $this->fs->remove($iter);
        }
    }

    public function isOptional(): bool
    {
        return false;
    }

    public function warmUp(string $cacheDir): array
    {
        $this->fs->mkdir(join_paths($cacheDir, self::subDir));

        return [];
    }

    public function backupFile(SplFileInfo $originFile): void
    {
        $targetFile = join_paths($this->cacheDir, $originFile->getBasename());
        $this->fs->copy($originFile->getPathname(), $targetFile, true);
    }

    public function getListing(): iterable
    {
        return (new Finder())
                        ->in($this->cacheDir)
                        ->files()
                        ->sortByModifiedTime()
                        ->getIterator();
    }

    public function createResponse(string $filename): BinaryFileResponse
    {
        $path = join_paths($this->cacheDir, $filename);
        if (!file_exists($path)) {
            throw new NotFoundHttpException($filename . ' does not exist');
        }

        return new BinaryFileResponse($path);
    }

    public function getFileInfo(string $filename): \SplFileInfo
    {
        $path = join_paths($this->cacheDir, $filename);
        if (!file_exists($path)) {
            throw new NotFoundHttpException($filename . ' does not exist');
        }

        return new \SplFileInfo($path);
    }

}
