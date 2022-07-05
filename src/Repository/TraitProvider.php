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
    const attributesPage = 'Attributs';
    const socNetCategory = 'Réseau social';

    public function __construct(MediaWiki $param, CacheInterface $cache)
    {
        $this->wiki = $param;
        $this->cache = $cache;
    }

    public function findSkills(): array
    {
        $skills = $this->cache->get('skill_list', function (ItemInterface $item) {
            $item->expiresAfter(DateInterval::createFromDateString('1 day'));

            return $this->wiki->searchPageFromCategory(self::skillCategory, 50);
        });

        usort($skills, function($a, $b) {
            return iconv('UTF-8', 'ASCII//TRANSLIT', $a->title) > iconv('UTF-8', 'ASCII//TRANSLIT', $b->title);
        });

        $listing = [];
        foreach ($skills as $item) {
            $listing[$item->title] = $item->title;
        }

        return $listing;
    }

    public function findAttributes(): array
    {
        return $this->cache->get('attribute_list', function (ItemInterface $item) {
                $item->expiresAfter(DateInterval::createFromDateString('1 day'));

                $content = $this->wiki->getPageByName(self::attributesPage);
                $doc = new \DOMDocument("1.0", "utf-8");
                $doc->loadXML($content);
                $xpath = new \DOMXpath($doc);
                $elements = $xpath->query("//tr/td[1]");

                for ($k = 0; $k < 5; $k++) {
                    $name = trim($elements->item($k)->textContent);
                    $listing[$name] = $name;
                }

                return $listing;
            });
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
