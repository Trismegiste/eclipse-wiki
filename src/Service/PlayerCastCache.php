<?php

/*
 * Eclipse Wiki
 */

namespace App\Service;

use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use function join_paths;

/**
 * Tree structure for storing temporary files to be casted for players
 */
class PlayerCastCache implements CacheWarmerInterface, CacheClearerInterface
{

    const subDir = 'player';

    protected $cacheDir;
    protected $fs;
    protected $maxDimension;
    protected $maxSize;

    public function __construct(Filesystem $fs, string $cacheDir, float $maxSize = 1e5, int $maxDim = 1000)
    {
        $this->fs = $fs;
        $this->cacheDir = join_paths($cacheDir, self::subDir);
        $this->maxDimension = (float) $maxDim;
        $this->maxSize = $maxSize;
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

    public function slimPictureForPush(\GdImage $gd2): \GdImage
    {
        // checking dimension of picture
        $sx = imagesx($gd2);
        $sy = imagesy($gd2);
        $maxSize = max([$sx, $sy]);
        if ($maxSize > $this->maxDimension) {
            $forPlayer = imagescale($gd2, intval($sx * $this->maxDimension / $maxSize), intval($sy * $this->maxDimension / $maxSize));
            imagedestroy($gd2);
        } else {
            $forPlayer = $gd2;
        }

        return $forPlayer;
    }

}
