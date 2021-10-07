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
abstract class GenericProvider
{

    protected $wiki;
    protected $cache;

    public function __construct(MediaWiki $param, CacheInterface $cache)
    {
        $this->wiki = $param;
        $this->cache = $cache;
    }

    abstract public function getListing(): array;

    abstract public function findOne(string $key); // @todo renvoyer une interface avec une methode getKey()

    protected function sanitize(string $key): string
    {
        return str_replace(['%', '-'], '_', urlencode($key));
    }

}
