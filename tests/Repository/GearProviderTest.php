<?php

use App\Entity\Gear;
use App\Entity\MediaWikiPage;
use App\Repository\GearProvider;
use PHPUnit\Framework\TestCase;
use Trismegiste\Toolbox\MongoDb\Repository;

/*
 * eclipse-wiki
 */

class GearProviderTest extends TestCase
{

    protected $sut;
    protected $repo;

    protected function setUp(): void
    {
        $this->repo = $this->createMock(Repository::class);
        $this->repo->expects($this->any())
                ->method('search')
                ->willReturn(new ArrayIterator([$this->createPage()]));

        $this->sut = new GearProvider($this->repo);
    }

    public function testFindOne()
    {
        $skill = $this->sut->findOne('Dummy');
        $this->assertEquals('Dummy', $skill->getName());
    }

    public function testFindAll()
    {
        $edge = $this->sut->getListing();
        $this->assertCount(1, $edge);
        $this->assertArrayHasKey('Dummy', $edge);
        $this->assertInstanceOf(Gear::class, $edge['Dummy']);
        $this->assertEquals('Dummy', $edge['Dummy']->getName());
    }

    protected function createPage(): MediaWikiPage
    {
        $dummy = new MediaWikiPage('Dummy', 'Matériel');
        $dummy->content = "zzzzzzzz";

        return $dummy;
    }

}
