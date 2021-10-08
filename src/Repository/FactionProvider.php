<?php

/*
 * Eclipse Wiki
 */

namespace App\Repository;

use App\Entity\Faction;
use DOMDocument;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Provider for Faction
 */
class FactionProvider extends GenericProvider
{

    public function findOne(string $key): \App\Entity\Indexable
    {
        return $this->cache->get('faction_page_' . $this->sanitize($key), function (ItemInterface $item) use ($key) {
                    $item->expiresAfter(\DateInterval::createFromDateString('1 day'));
                    $content = $this->wiki->getPageByName($key);
                    $doc = new DOMDocument("1.0", "utf-8");
                    $doc->loadXML($content);

                    $xpath = new \DOMXpath($doc);
                    $obj = new Faction($key);

                    // abilities, thanks to http://xpather.com/
                    $elements = $xpath->query("//ul[1]/li[text()]");
                    foreach ($elements as $li) {
                        $obj->characteristic[] = trim($li->nodeValue);
                    }

                    // motivations
                    $elements = $xpath->query("//span[@id='Motivations_sugg.C3.A9r.C3.A9es']/parent::h2/following-sibling::ul[1]/li[text()]");
                    foreach ($elements as $li) {
                        $obj->motivation[] = trim($li->nodeValue);
                    }

                    return $obj;
                });
    }

    public function getListing(): array
    {
        return $this->cache->get('faction_list', function (ItemInterface $item) {
                    $item->expiresAfter(\DateInterval::createFromDateString('1 day'));
                    $bg = $this->wiki->searchPageFromCategory('Faction', 50);

                    $listing = [];
                    foreach ($bg as $item) {
                        $listing[$item->title] = $item->title;
                    }

                    return $listing;
                });
    }

}
