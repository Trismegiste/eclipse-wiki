<?php

/*
 * Eclipse Wiki
 */

namespace App\Repository;

use App\Service\MediaWiki;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * CachedProvider uses cached API requests
 */
abstract class CachedProvider implements GenericProvider
{

    protected $wiki;
    protected $cache;

    public function __construct(MediaWiki $param, CacheInterface $cache)
    {
        $this->wiki = $param;
        $this->cache = $cache;
    }

    /**
     * Sanitizes a string for using as a cache key
     * @param string $key
     * @return string
     */
    protected function sanitize(string $key): string
    {
        return str_replace(['%', '-'], '_', urlencode($key));
    }

}
