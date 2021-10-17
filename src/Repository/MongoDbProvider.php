<?php

/*
 * eclipse-wiki
 */

namespace App\Repository;

use App\Entity\Indexable;
use App\Entity\MediaWikiPage;
use Trismegiste\Toolbox\MongoDb\Repository;

/**
 * For MongoDb
 */
abstract class MongoDbProvider implements GenericProvider
{

    protected $repository;

    public function __construct(Repository $pageRepo)
    {
        $this->repository = $pageRepo;
    }

    public function findOne(string $key): Indexable
    {
        $it = $this->repository->search(['category' => $this->getCategory(), 'title' => $key]);
        $it->rewind();
        $page = $it->current();

        return $this->createFromPage($page);
    }

    public function getListing(): array
    {
        $it = $this->repository->search(['category' => $this->getCategory()]);

        $listing = [];
        foreach ($it as $page) {
            $listing[$page->getTitle()] = $this->createFromPage($page);
        }

        return $listing;
    }

    protected function getFirstTextContent(\DOMXpath $xpath, string $query): string
    {
        $elements = $xpath->query($query);

        return trim($elements->item(0)->textContent);
    }

    abstract protected function createFromPage(MediaWikiPage $page): Indexable;

    abstract protected function getCategory(): string;
}
