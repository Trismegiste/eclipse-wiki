<?php

/*
 * eclipse-wiki
 */

namespace App\Repository;

use App\Service\MediaWiki;
use DateInterval;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Description of TraitProvider
 *
 * @author flo
 */
class TraitProvider
{

    protected $wiki;
    protected $cache;

    public function __construct(MediaWiki $param, \Symfony\Contracts\Cache\CacheInterface $cache)
    {
        $this->wiki = $param;
        $this->cache = $cache;
    }

    public function findAll(string $cat): array
    {
        return $this->cache->get('skill_list', function (ItemInterface $item) {
                $item->expiresAfter(DateInterval::createFromDateString('1 day'));
                $listing = $this->wiki->searchPageFromCategory('Compétence', 50);

                return $listing;
            });
    }

}
