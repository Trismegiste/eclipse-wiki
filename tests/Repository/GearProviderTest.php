<?php

/*
 * eclipse-wiki
 */

use App\Entity\Gear;
use App\Entity\Indexable;
use App\Entity\MediaWikiPage;
use App\Repository\GearProvider;
use App\Repository\MongoDbProvider;
use App\Tests\Repository\MongoDbProviderTestCase;
use Trismegiste\Toolbox\MongoDb\Repository;

class GearProviderTest extends MongoDbProviderTestCase
{

    protected function createPage(): MediaWikiPage
    {
        $dummy = new MediaWikiPage('Dummy', 'Matériel');
        $dummy->content = "zzzzzzzz";

        return $dummy;
    }

    protected function createProvider(Repository $repo): MongoDbProvider
    {
        return new GearProvider($repo);
    }

    protected function assertDetail(Indexable $obj)
    {
        $this->assertInstanceOf(Gear::class, $obj);
    }

}
