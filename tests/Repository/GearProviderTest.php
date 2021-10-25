<?php

use App\Entity\Gear;
use App\Entity\MediaWikiPage;
use App\Repository\GearProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/*
 * eclipse-wiki
 */

class GearProviderTest extends KernelTestCase
{

    protected function setUp(): void
    {
        self::createKernel();
        $this->sut = new GearProvider(self::getContainer()->get('app.mwpage.repository'));
    }

    public function testInsertData()
    {
        $repo = self::getContainer()->get('app.mwpage.repository');
        $it = $repo->search();
        $repo->delete(iterator_to_array($it));

        $dummy = new MediaWikiPage('Dummy', 'Matériel');
        $dummy->content = "zzzzzzzz";
        $repo->save($dummy);
        $it = $repo->search();
        $this->assertCount(1, iterator_to_array($it));
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

}
