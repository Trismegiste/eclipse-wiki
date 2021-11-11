<?php

/*
 * Eclipse Wiki
 */

namespace App\Repository;

use App\Entity\Indexable;
use App\Entity\MediaWikiPage;
use App\Entity\Armor;
use Trismegiste\Toolbox\MongoDb\Repository;

/**
 * ArmorProvider
 */
class ArmorProvider implements GenericProvider
{

    protected $repository;

    public function __construct(Repository $pageRepo)
    {
        $this->repository = $pageRepo;
    }

    public function findOne(string $key): Indexable
    {
        $lst = $this->getListing();
        foreach ($lst as $w) {
            if ($w->name === $key) {
                return $w;
            }
        }
    }

    public function getListing(): array
    {
        /** @var MediaWikiPage $page */
        $it = $this->repository->search(['title' => 'Armures']);
        $it->rewind();
        $page = $it->current();
        preg_match('#\{\|([^\}]+)\|\}#', $page->content, $table);
        $rows = explode('|-', $table[1]);
        array_shift($rows);
        array_shift($rows);

        $listing = [];
        foreach ($rows as $row) {
            $cells = explode('|', $row);
            $listing[] = new Armor(
                trim($cells[1]),
                trim($cells[2]),
                trim($cells[3]),
                trim($cells[4])
            );
        }

        return $listing;
    }

}
