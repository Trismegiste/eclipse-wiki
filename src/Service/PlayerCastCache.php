<?php

/*
 * Eclipse Wiki
 */

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use function join_paths;

/**
 * Folder for storing temporary files to be broadcasted to players
 */
class PlayerCastCache implements CacheWarmerInterface, CacheClearerInterface
{

    const subDir = 'player';

    protected $cacheDir;
    protected $fs;

    public function __construct(Filesystem $fs, string $cacheDir)
    {
        $this->fs = $fs;
        $this->cacheDir = join_paths($cacheDir, self::subDir);
    }

    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        $this->fs->mkdir(join_paths($cacheDir, self::subDir));

        return [];
    }

    public function isOptional(): bool
    {
        return false;
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

}
