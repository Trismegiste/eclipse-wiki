<?php

/*
 * eclipse-wiki
 */

namespace App\Repository;

use App\Entity\Attribute;
use App\Entity\Indexable;
use DateInterval;
use DOMDocument;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Provider for Attributes
 */
class AttributeProvider extends CachedProvider
{

    public function findOne(string $key): Indexable
    {
        $listing = $this->getListing();

        return $listing[$key];
    }

    public function getListing(): array
    {
        return $this->cache->get('attribute_object_list', function (ItemInterface $item) {
                    $item->expiresAfter(DateInterval::createFromDateString('1 day'));

                    $content = $this->wiki->getPageByName('Attributs');
                    $doc = new DOMDocument("1.0", "utf-8");
                    $doc->loadXML($content);
                    $xpath = new \DOMXpath($doc);
                    $elements = $xpath->query("//tr/td[1]");

                    for ($k = 0; $k < 5; $k++) {
                        $name = trim($elements->item($k)->textContent);
                        $listing[$name] = new Attribute($name);
                    }

                    return $listing;
                });
    }

}
