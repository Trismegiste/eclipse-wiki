<?php

/*
 * eclipse-wiki
 */

namespace App\Repository;

use DOMDocument;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Provider for morph
 */
class MorphProvider extends GenericProvider
{

    public function findOne(string $key)
    {
        $content = $this->wiki->getPageByName($key);
        $doc = new DOMDocument("1.0", "utf-8");
        $doc->loadXML($content);

        $xpath = new \DOMXpath($doc);
        $obj = new \App\Entity\Morph($key);

        // abilities, thanks to http://xpather.com/
        $elements = $xpath->query("//span[@id='Avantages']/parent::h2/following-sibling::ul[1]/li[text()]");
        foreach ($elements as $li) {
            $obj->ability[] = trim($li->nodeValue);
        }
        // disabiilties
        $elements = $xpath->query("//span[@id='D.C3.A9savantages']/parent::h2/following-sibling::ul[1]/li[text()]");
        foreach ($elements as $li) {
            $obj->disability[] = trim($li->nodeValue);
        }

        $elements = $xpath->query("//div[@data-source='type']/child::div");
        $obj->type = $elements->item(0)->textContent;

        $elements = $xpath->query("//div[@data-source='cout']/child::div");
        $obj->price = $elements->item(0)->textContent;

        return $obj;
    }

    public function getListing(): array
    {
        return $this->cache->get('morph_list', function (ItemInterface $item) {

                $doc = new DOMDocument("1.0", "utf-8");
                $content = $this->wiki->getPageByName('Type de Morphe');
                $doc->loadXML(strip_tags($content, '<div><ul><li><a>'));
                $xpath = new \DOMXpath($doc);

                $elements = $xpath->query("//ul[1]/li/a[contains(@href,'gorie:')]/@href");
                $listing = [];
                foreach ($elements as $li) {
                    if (preg_match('#rie:(.+)$#', urldecode($li->nodeValue), $match)) {
                        $category = $match[1];
                        $morph = $this->wiki->searchPageFromCategory($category, 50);
                        foreach ($morph as $item) {
                            $listing[$category][$item->title] = $item->title;
                        }
                    }
                }

                return $listing;
            });
    }

}
