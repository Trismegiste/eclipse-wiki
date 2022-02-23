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
        $obj = new Gear();
        $obj->setName($page->getTitle());
        $param = $this->getOrderedParametersFromTemplate('PrixMatos', $page->content, ['var.']);
        $obj->price = $param[0];

        return $obj;
    }

    protected function getCategory(): string
    {
        return 'Matériel';
    }

}
