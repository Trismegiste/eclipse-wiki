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
 * Description of TraitProvider
 *
 * @author flo
 */
class TraitProvider
{

    protected $wiki;
    protected $cache;

    public function __construct(MediaWiki $param, CacheInterface $cache)
    {
        $this->wiki = $param;
        $this->cache = $cache;
    }

    public function findSkills(): array
    {
        $skills = $this->cache->get('skill_list', function (ItemInterface $item) {
            $item->expiresAfter(DateInterval::createFromDateString('1 day'));

            return $this->wiki->searchPageFromCategory('Compétence', 50);
        });

        $listing = [];
        foreach ($skills as $item) {
            $listing[$item->title] = $item->title;
        }

        return $listing;
    }

    public function findAttributes(): array
    {
        return $this->cache->get('attributes_list', function (ItemInterface $item) {
                $item->expiresAfter(DateInterval::createFromDateString('1 day'));

                $content = $this->wiki->getPageByName('Attributs');
                $doc = new \DOMDocument("1.0", "utf-8");
                $doc->loadXML($content);
                $xpath = new \DOMXpath($doc);
                $elements = $xpath->query("//tr/td");

                for ($k = 0; $k < 5; $k++) {
                    $name = $elements->item(3 * $k);
                    $listing[$name->textContent] = $name->textContent;
                }

                return $listing;
            });
    }

    public function findSocialNetworks(): array
    {
        $skills = $this->cache->get('socialnetwork_list', function (ItemInterface $item) {
            $item->expiresAfter(DateInterval::createFromDateString('1 day'));

            return $this->wiki->searchPageFromCategory('Réseau social', 10);
        });

        $listing = [];
        foreach ($skills as $item) {
            $listing[$item->title] = $item->title;
        }

        return $listing;
    }

}
