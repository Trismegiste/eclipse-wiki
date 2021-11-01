<?php

/*
 * Eclipse Wiki
 */

namespace App\Repository;

use App\Entity\Indexable;
use App\Entity\Morph;
use DateInterval;
use DOMDocument;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Description of ShellProvider
 */
class ShellProvider extends CachedProvider
{

    public function findOne(string $key): Indexable
    {
        return $this->cache->get('shell_page_' . $this->sanitize($key), function (ItemInterface $item) use ($key) {
                $item->expiresAfter(\DateInterval::createFromDateString('1 day'));
                $content = $this->wiki->getPageByName($key);
                $doc = new DOMDocument("1.0", "utf-8");
                $doc->loadXML($content);

                $xpath = new \DOMXpath($doc);
                $obj = new Morph($key);
                $obj->type = 'Coquille';

                // abilities, thanks to http://xpather.com/
                $elements = $xpath->query("//ul[1]/li[text()]");
                foreach ($elements as $li) {
                    $obj->ability[] = trim($li->nodeValue);
                }

                return $obj;
            });
    }

    public function getListing(): array
    {
        return $this->cache->get('shell_list', function (ItemInterface $item) {
                $item->expiresAfter(DateInterval::createFromDateString('1 day'));
                $bg = $this->wiki->searchPageFromCategory('Coquille', 50);

                $listing = [];
                foreach ($bg as $item) {
                    $listing[$item->title] = $item->title;
                }

                return $listing;
            });
    }

}
