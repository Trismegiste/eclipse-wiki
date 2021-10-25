<?php

/*
 * eclipse-wiki
 */

namespace App\Repository;

use App\Entity\Gear;
use App\Entity\Indexable;
use App\Entity\MediaWikiPage;

/**
 * Provider for gear & stuff
 */
class GearProvider extends MongoDbProvider
{

    protected function createFromPage(MediaWikiPage $page): Indexable
    {
        return new Gear($page->getTitle());
    }

    protected function getCategory(): string
    {
        return 'Matériel';
    }

}
