<?php

/*
 * eclipse-wiki
 */

namespace App\Repository;

use App\Entity\Indexable;
use App\Entity\MediaWikiPage;
use Trismegiste\Strangelove\MongoDb\Repository;

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
        if (is_null($page)) {
            throw new \RuntimeException("$key is not a valid page in " . $this->getCategory());
        }

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

    protected function getNamedParametersFromTemplate(string $name, string $content, array $default = []): array
    {
        $match = [];
        $param = $default;
        if (preg_match('#\{\{' . $name . '\|([^\}]+)\}\}#', $content, $match)) {
            $paramStr = explode('|', $match[1]);
            foreach ($paramStr as $assoc) {
                preg_match('#([^=]+)=([^=]+)#', $assoc, $kv);
                $param[$kv[1]] = $kv[2];
            }
        }

        return $param;
    }

    protected function getOrderedParametersFromTemplate(string $name, string $content, array $default = []): array
    {
        $match = [];
        $param = $default;
        if (preg_match('#\{\{' . $name . '\|([^\}]+)\}\}#', $content, $match)) {
            $paramStr = explode('|', $match[1]);
            foreach ($paramStr as $idx => $val) {
                $param[$idx] = $val;
            }
        }

        return $param;
    }

    abstract protected function createFromPage(MediaWikiPage $page): Indexable;

    abstract protected function getCategory(): string;
}
