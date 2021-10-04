<?php

/*
 * eclipse-wiki
 */

namespace App\Repository;

/**
 * Provider for morph
 */
class MorphProvider extends GenericProvider
{

    public function findOne(string $key)
    {
        
    }

    public function getListing(): array
    {
        $doc = new \DOMDocument("1.0", "utf-8");
        $content = $this->wiki->getPageByName('Type de Morphe');
        $doc->loadXML(strip_tags($content, '<div><ul><li><a>'));
        $xpath = new \DOMXpath($doc);

        $elements = $xpath->query("//ul[1]/li/a[contains(@href,'gorie:')]/@href");
        foreach ($elements as $li) {
            var_dump($li->nodeValue);
        }

        return [];
    }

}
