<?php

/*
 * eclipse-wiki
 */

namespace App\Repository;

use App\Service\MediaWiki;
use DateInterval;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Provider for all Traits : Attributes, Skills & Social Networks
 */
class TraitProvider
{

    protected $wiki;
    protected $cache;

    const skillCategory = 'Compétence';
    const socNetCategory = 'Réseau social';

    public function __construct(MediaWiki $param, CacheInterface $cache, protected AttributeProvider $attributesRepo)
    {
        $this->wiki = $param;
        $this->cache = $cache;
    }

    public function findSkills(): array
    {
        // @todo Using SkillProvider here (like I've done below for findAttributes method) should be D.R.Y
        // but the behavior of that repository is very different since it's calling MongoDb cached pages
        // I don't think it's a good idea. 
        // Granted it'll be local but also far more bloated since we'll instantiate 
        // a bunch of complex objects from the DB just to keep the title. Silly.
        $skills = $this->cache->get('skill_list', function (ItemInterface $item) {
            $item->expiresAfter(DateInterval::createFromDateString('1 day'));

            return $this->wiki->searchPageFromCategory(self::skillCategory, 50);
        });

        usort($skills, function ($a, $b) {
            return strcmp(iconv('UTF-8', 'ASCII//TRANSLIT', $a->title), iconv('UTF-8', 'ASCII//TRANSLIT', $b->title));
        });

        $listing = [];
        foreach ($skills as $item) {
            $listing[$item->title] = $item->title;
        }

        return $listing;
    }

    public function findAttributes(): array
    {
        $listing = $this->attributesRepo->getListing();
        array_walk($listing, function (&$v, $k) {
            $v = $k;
        });

        return $listing;
    }

    public function findSocialNetworks(): array
    {
        $skills = $this->cache->get('socialnetwork_list', function (ItemInterface $item) {
            $item->expiresAfter(DateInterval::createFromDateString('1 day'));

            return $this->wiki->searchPageFromCategory(self::socNetCategory, 10);
        });

        $listing = [];
        foreach ($skills as $item) {
            $listing[$item->title] = $item->title;
        }

        return $listing;
    }

}
