<?php

/*
 * eclipse-wiki
 */

namespace App\Repository;

use App\Entity\Edge;
use App\Entity\MediaWikiPage;
use DOMDocument;

/**
 * Provider for Edges
 */
class EdgeProvider extends MongoDbProvider
{

    protected function createFromPage(MediaWikiPage $page): \App\Entity\Indexable
    {
        $doc = new DOMDocument("1.0", "utf-8");
        $doc->loadXML($page->content);

        $xpath = new \DOMXpath($doc);
        $category = $this->getFirstTextContent($xpath, "//div[@data-source='type']/div[1]/p");
        $rank = $this->getFirstTextContent($xpath, "//div[@data-source='rang']/div[1]/p");
        $ego = $this->getFirstTextContent($xpath, "//div[@data-source='ego']/div[1]") === 'Oui';
        $bio = $this->getFirstTextContent($xpath, "//div[@data-source='bio']/div[1]") === 'Oui';
        $synth = $this->getFirstTextContent($xpath, "//div[@data-source='synth']/div[1]") === 'Oui';
        $requis = $this->getFirstTextContent($xpath, "//tr[contains(th, 'Prérequis')]/td");

        return new Edge($page->getTitle(), $rank, $category, $ego, $bio, $synth, $requis);
    }

    protected function getCategory(): string
    {
        return 'Atout';
    }

}
