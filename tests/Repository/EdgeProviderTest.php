<?php

/*
 * eclipse-wiki
 */

use App\Entity\Edge;
use App\Entity\MediaWikiPage;
use App\Repository\EdgeProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Description of EdgeProviderTest
 *
 * @author flo
 */
class EdgeProviderTest extends KernelTestCase
{

    protected function setUp(): void
    {
        self::createKernel();
        $this->sut = self::getContainer()->get(EdgeProvider::class);
    }

    public function testInsertData()
    {
        $repo = self::getContainer()->get('app.mwpage.repository');
        $it = $repo->search();
        $repo->delete(iterator_to_array($it));

        $dummy = new MediaWikiPage('Dummy', 'Atout');
        $dummy->content = "{{SaWoAtout|ego=1|type=pro|rang=n|src=EP}}xxxxxxxxxxxx";
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
        $this->assertInstanceOf(Edge::class, $edge['Dummy']);
        $this->assertEquals('Dummy', $edge['Dummy']->getName());
    }

}
