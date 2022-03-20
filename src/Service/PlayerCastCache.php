<?php

/*
 * Eclipse Wiki
 */

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Tree structure for storing temporary files to be casted for players
 */
class PlayerCastCache implements CacheWarmerInterface, CacheClearerInterface
{

    const subDir = 'player';

    protected $fs;

    public function __construct(Filesystem $fs)
    {
        $this->fs = $fs;
    }

    public function warmUp(string $cacheDir)
    {
        $this->fs->mkdir(join_paths($cacheDir, self::subDir));
    }

    public function isOptional(): bool
    {
        return false;
    }

    public function clear(string $cacheDir)
    {
        
    }

}
