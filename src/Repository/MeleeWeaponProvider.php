<?php

/*
 * Eclipse Wiki
 */

namespace App\Repository;

use App\Entity\Indexable;
use App\Entity\MediaWikiPage;
use App\Entity\MeleeWeapon;
use Trismegiste\Toolbox\MongoDb\Repository;

/**
 * MeleeWeaponProvider
 */
class MeleeWeaponProvider implements GenericProvider
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
        $it = $this->repository->search(['title' => 'Armes de mêlée']);
        $it->rewind();
        $page = $it->current();
        preg_match('#\{\|([^\}]+)\|\}#', $page->content, $table);
        $rows = explode('|-', $table[1]);
        array_shift($rows);
        array_shift($rows);

        $listing = [];
        foreach ($rows as $row) {
            $cells = explode('|', $row);
            $w = new MeleeWeapon(
                trim($cells[1]),
                trim($cells[2]),
                (int) trim($cells[3])
            );
            $w->minStr = substr(trim($cells[4]), 1);
            $listing[] = $w;
        }

        return $listing;
    }

}
