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
        $this->maxDimension = $maxDim;
        $this->maxSize = $maxSize;
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
        $this->fs->remove(join_paths($cacheDir, self::subDir));
    }

    public function slimPictureForPush(SplFileInfo $picture): SplFileInfo
    {
        if ($picture->getSize() < $this->maxSize) {
            // small image, do nothing
            return $picture;
        }

        // this picture is big, need to reduce its size
        $gd2 = imagecreatefromstring(file_get_contents($picture->getPathname()));
        // checking dimension of picture
        $maxSize = max([imagesx($gd2), imagesy($gd2)]);
        if ($maxSize > $this->maxDimension) {
            $forPlayer = imagescale($gd2, imagesx($gd2) * (float) $this->maxDimension / $maxSize, imagesy($gd2) * (float) $this->maxDimension / $maxSize);
            imagedestroy($gd2);
        } else {
            $forPlayer = $gd2;
        }

        $compressedPicture = join_paths($this->cacheDir, $picture->getBasename('.' . $picture->getExtension()) . '.jpg');
        imagejpeg($forPlayer, $compressedPicture, 60);

        return new SplFileInfo($compressedPicture);
    }

}
