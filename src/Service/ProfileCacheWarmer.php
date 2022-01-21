<?php

/*
 * Eclipse Wiki
 */

namespace App\Service;

/**
 * Description of ProfileCacheWarmer
 */
class ProfileCacheWarmer implements \Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface
{

    public function isOptional(): bool
    {
        return true;
    }

    public function warmUp(string $cacheDir): array
    {
        $fs = new \Symfony\Component\Filesystem\Filesystem();
        $cacheDir = join_paths($cacheDir, 'profile');
        if (!$fs->exists($cacheDir)) {
            $fs->mkdir($cacheDir);
        }
        
        return [];
    }

}
