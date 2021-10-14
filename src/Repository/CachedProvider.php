<?php

/*
 * Eclipse Wiki
 */

namespace App\Repository;

use App\Service\MediaWiki;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Description of GenericProvider
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

    protected function sanitize(string $key): string
    {
        return str_replace(['%', '-'], '_', urlencode($key));
    }

}
