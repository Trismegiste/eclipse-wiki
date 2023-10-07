<?php

/*
 * Eclipse Wiki
 */

namespace App\Service\StableDiffusion;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Tree structure for local storage of invoke ai picture
 */
class LocalWarmer implements CacheWarmerInterface
{

    protected $fs;
    protected $root;

    public function __construct(Filesystem $fs, LocalRepository $store)
    {
        $this->fs = $fs;
        $this->root = $store->getRootDir();
    }

    public function warmUp(string $cacheDir): array
    {
        $this->fs->mkdir($this->root);

        return [];
    }

    public function isOptional(): bool
    {
        return false;
    }

}
