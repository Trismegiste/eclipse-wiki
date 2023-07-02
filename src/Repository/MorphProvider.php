<?php

/*
 * eclipse-wiki
 */

namespace App\Repository;

use App\Entity\Hindrance;
use App\Entity\Indexable;
use App\Entity\Morph;
use App\Entity\TraitBonus;
use App\Service\MediaWiki;
use DateInterval;
use DOMDocument;
use DOMXPath;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Provider for morph
 */
class MorphProvider extends CachedProvider
{

    public function __construct(MediaWiki $param, CacheInterface $cache, protected EdgeProvider $edgeRepo, protected HindranceProvider $hindRepo)
    {
        parent::__construct($param, $cache);
    }

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

    /*
     * {{RaceBonusAtttribut|1|VIG}}
     * {{RaceAtout|Sang-froid}}
     * {{RaceBonusCompétence|1|Combat}}
     * {{RaceHandicap|m|Loyal}}
     */

    protected function hydrateWithTree(Morph $obj, DOMDocument $doc): void
    {
        $crawler = new DOMXPath($doc);

        // Extracts bonus on Skills
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

        // Extracts edges
        $edgeList = [];
        $iter = $crawler->query('//h[@level=2][contains(text(), "Avantage")]/following-sibling::template/title[normalize-space()="RaceAtout"]/parent::template');
        foreach ($iter as $edge) {
            $paramIter = $crawler->query('part/name[@index="1"]/following-sibling::value', $edge);
            $key = $paramIter->item(0)->nodeValue;
            try {
                /** @var \App\Entity\Edge $found */
                $found = $this->edgeRepo->findOne($key);
                $found->origin = 'Morphe';
                $edgeList[] = $found;
            } catch (\RuntimeException $e) {
                // skip unknown edge
            }
        }
        $obj->setEdges($edgeList);

        // Extracts Hindrances
        $hindList = [];
        $convertLevel = ['m' => Hindrance::MINOR, 'M' => Hindrance::MAJOR];
        $iter = $crawler->query('//h[@level=2][contains(text(), "savantage")]/following-sibling::template/title[normalize-space()="RaceHandicap"]/parent::template');
        foreach ($iter as $hind) {
            $paramIter = $crawler->query('part/name[@index="1"]/following-sibling::value', $hind);
            $level = $paramIter->item(0)->nodeValue;
            $paramIter = $crawler->query('part/name[@index="2"]/following-sibling::value', $hind);
            $key = $paramIter->item(0)->nodeValue;
            try {
                /** @var Hindrance $found */
                $found = $this->hindRepo->findOne($key);
                $found->setLevel($convertLevel[$level]);
                $found->origin = 'Morphe';
                $hindList[] = $found;
            } catch (\RuntimeException $e) {
                // skip unknown hindrance
            }
        }
        $obj->setHindrances($hindList);
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
