<?php

/*
 * Eclipse Wiki
 */

namespace App\Repository;

use App\Entity\Indexable;
use App\Entity\MediaWikiPage;
use App\Entity\RangedWeapon;
use Trismegiste\Toolbox\MongoDb\Repository;

/**
 * RangedWeaponProvider
 */
class RangedWeaponProvider implements GenericProvider
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
        $listing = [];
        /** @var MediaWikiPage $page */
        $it = $this->repository->search(['title' => 'Armes à distance']);
        $it->rewind();
        $page = $it->current();

        if (!is_null($page)) {
            preg_match('#\{\|([^\}]+)\|\}#', $page->content, $table);
            $rows = explode('|-', $table[1]);
            array_shift($rows);
            array_shift($rows);

            foreach ($rows as $row) {
                $cells = explode('|', $row);
                $w = new RangedWeapon(
                        trim($cells[1]) . ' ' . trim($cells[2]) . ' (' . trim($cells[12]) . ')',
                        trim($cells[4]),
                        (int) trim($cells[5]),
                        (int) trim($cells[6]),
                        trim($cells[3])
                );
                $w->minStr = substr(trim($cells[8]), 1);
                $listing[] = $w;
            }
        }

        return $listing;
    }

}
