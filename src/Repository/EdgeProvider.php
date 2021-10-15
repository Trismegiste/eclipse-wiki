<?php

/*
 * eclipse-wiki
 */

namespace App\Repository;

use App\Entity\Edge;
use App\Entity\Indexable;
use DOMDocument;

/**
 * Provider for Edges
 */
class EdgeProvider extends MongoDbProvider
{

    public function findOne(string $key): Indexable
    {
        $it = $this->repository->search(['category' => 'Atout', 'title' => $key]);
        $it->rewind();
        $page = $it->current();

        $doc = new DOMDocument("1.0", "utf-8");
        $doc->loadXML($page->content);

        $xpath = new \DOMXpath($doc);
        $category = $this->getFirstTextContent($xpath, "//div[@data-source='type']/div[1]/p");
        $rank = $this->getFirstTextContent($xpath, "//div[@data-source='rang']/div[1]/p");
        $ego = $this->getFirstTextContent($xpath, "//div[@data-source='ego']/div[1]") === 'Oui';
        $bio = $this->getFirstTextContent($xpath, "//div[@data-source='bio']/div[1]") === 'Oui';
        $synth = $this->getFirstTextContent($xpath, "//div[@data-source='synth']/div[1]") === 'Oui';
        $requis = $this->getFirstTextContent($xpath, "//tr[contains(th, 'Prérequis')]/td");

        return new Edge($key, $rank, $category, $ego, $bio, $synth, $requis);
    }

    public function getListing(): array
    {
        $it = $this->repository->search(['category' => 'Atout']);

        $listing = [];
        foreach ($it as $edge) {
            $listing[$edge->getTitle()] = $edge->getTitle();
        }

        return $listing;
    }

    protected function getFirstTextContent(\DOMXpath $xpath, string $query): string
    {
        $elements = $xpath->query($query);

        return trim($elements->item(0)->textContent);
    }

}
