<?php

/*
 * Eclipse Wiki
 */

namespace App\Repository;

use App\Entity\Background;
use DOMDocument;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Description of BackgroundProvider
 */
class BackgroundProvider extends GenericProvider
{

    public function findOne(string $key)
    {
        $content = $this->wiki->getPageByName($key);
        $doc = new DOMDocument("1.0", "utf-8");
        $doc->loadXML($content);
        $xpath = new \DOMXpath($doc);
        $obj = new Background();

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

        // motivations
        $elements = $xpath->query("//span[@id='Motivations_sugg.C3.A9r.C3.A9es']/parent::h2/following-sibling::ul[1]/li[text()]");
        foreach ($elements as $li) {
            $obj->motivation[] = trim($li->nodeValue);
        }

        return $obj;
    }

    public function getListing(): array
    {
        return $this->cache->get('background_list', function (ItemInterface $item) {
                $bg = $this->wiki->searchPageFromCategory('Historique', 50);

                $listing = [];
                foreach ($bg as $item) {
                    $listing[$item->title] = $item->title;
                }

                return $listing;
            });
    }

}
