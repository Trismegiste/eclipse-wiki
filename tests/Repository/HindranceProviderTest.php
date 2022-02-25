<?php

/*
 * eclipse-wiki
 */

namespace App\Tests\Repository;

use App\Entity\Hindrance;
use App\Entity\Indexable;
use App\Entity\MediaWikiPage;
use App\Repository\HindranceProvider;
use App\Repository\MongoDbProvider;
use Trismegiste\Toolbox\MongoDb\Repository;

/**
 * Description of HindranceProviderTest
 *
 * @author trismegiste
 */
class HindranceProviderTest extends MongoDbProviderTestCase
{

    protected function assertDetail(Indexable $obj)
    {
        $this->assertInstanceOf(Hindrance::class, $obj);
        $this->assertTrue($obj->isEgo());
        $this->assertTrue($obj->isSynth());
        $this->assertTrue($obj->isBio());
        $this->assertTrue($obj->isBio());
        $this->assertEquals(Hindrance::MAJOR, $obj->getChoices());
    }

    protected function createPage(): MediaWikiPage
    {
        $dummy = new MediaWikiPage('Dummy', 'Handicap');
        $dummy->content = "zzzzzzzz {{SaWoHandicap|ego=1|bio=1|synth=1|type=M}}";

        return $dummy;
    }

    protected function createProvider(Repository $repo): MongoDbProvider
    {
        return new HindranceProvider($repo);
    }

}
