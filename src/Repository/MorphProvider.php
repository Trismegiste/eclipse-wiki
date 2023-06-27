<?php

/*
 * eclipse-wiki
 */

namespace App\Repository;

use App\Entity\Indexable;
use App\Entity\Morph;
use App\Entity\TraitBonus;
use DateInterval;
use DOMDocument;
use DOMXPath;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Provider for morph
 */
class MorphProvider extends CachedProvider
{

    public function findOne(string $key): Indexable
    {
        return $this->cache->get('morph_page_' . $this->sanitize($key), function (ItemInterface $item) use ($key) {
                    $item->expiresAfter(DateInterval::createFromDateString('1 day'));

                    $page = $this->wiki->getTreeAndHtmlDomByName($key);
                    $obj = new Morph($key);
                    $this->hydrateWithHtml($obj, $page['html']);
                    $this->hydrateWithTree($obj, $page['tree']);

                    return $obj;
                });
    }

    protected function hydrateWithHtml(Morph $obj, DOMDocument $doc): void
    {
        $xpath = new \DOMXpath($doc);

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
    }

    protected function hydrateWithTree(Morph $obj, DOMDocument $doc): void
    {
        // Extracts bonus on Skills
        $crawler = new DOMXPath($doc);
        $iter = $crawler->query('//h[@level=2][contains(text(), "Avantage")]/following-sibling::template/title[normalize-space()="RaceBonusCompétence"]/parent::template');
        foreach ($iter as $bonus) {
            $paramIter = $crawler->query('part/name[@index="2"]/following-sibling::value', $bonus);
            $skill = $paramIter->item(0)->nodeValue;
            $paramIter = $crawler->query('part/name[@index="1"]/following-sibling::value', $bonus);
            $bonus = $paramIter->item(0)->nodeValue;
            $obj->skillBonus[$skill] = new TraitBonus($bonus);
        }

        // Extracts bonus on Attributes
        $iter = $crawler->query('//h[@level=2][contains(text(), "Avantage")]/following-sibling::template/title[normalize-space()="RaceBonusAtttribut"]/parent::template');
        foreach ($iter as $bonus) {
            $paramIter = $crawler->query('part/name[@index="2"]/following-sibling::value', $bonus);
            $attr = $paramIter->item(0)->nodeValue;
            $paramIter = $crawler->query('part/name[@index="1"]/following-sibling::value', $bonus);
            $bonus = $paramIter->item(0)->nodeValue;
            $obj->attributeBonus[$attr] = new TraitBonus($bonus);
        }
    }

    public function getListing(): array
    {
        return $this->cache->get('morph_list', function (ItemInterface $item) {
                    $item->expiresAfter(DateInterval::createFromDateString('1 day'));

                    $doc = $this->wiki->getDocumentByName('Type de Morphe');
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
