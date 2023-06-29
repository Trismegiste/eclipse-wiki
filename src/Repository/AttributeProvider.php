<?php

/*
 * eclipse-wiki
 */

namespace App\Repository;

use App\Entity\Attribute;
use App\Entity\Indexable;
use DateInterval;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Provider for Attributes
 */
class AttributeProvider extends CachedProvider
{

    const attributesPage = 'Attributs';
    const attributesCount = 5; // if it changes, it's no longer the same rpg

    public function findOne(string $key): Indexable
    {
        $listing = $this->getListing();

        return $listing[$key];
    }

    public function getListing(): array
    {
        return $this->cache->get('attribute_object_list', function (ItemInterface $item) {
                    $item->expiresAfter(DateInterval::createFromDateString('1 day'));

                    $doc = $this->wiki->getDocumentByName(self::attributesPage);
                    $xpath = new \DOMXpath($doc);
                    $elements = $xpath->query("//tr/td[1]");

                    for ($k = 0; $k < self::attributesCount; $k++) {
                        $name = trim($elements->item($k)->textContent);
                        $listing[$name] = new Attribute($name);
                    }

                    return $listing;
                });
    }

}
