<?php

/*
 * Eclipse Wiki
 */

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Tree structure for storage
 */
class StorageWarmer implements CacheWarmerInterface
{

    protected $fs;
    protected $root;

    public function __construct(Filesystem $fs, Storage $store)
    {
        $this->fs = $fs;
        $this->root = $store->getRootDir();
    }

    public function warmUp(string $cacheDir)
    {
        $this->fs->mkdir($this->root);
    }

    public function isOptional(): bool
    {
        return false;
    }

}
