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

    protected $sut;

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
        $dummy->content = "{{SaWoAtout|ego=1|type=pro|rang=n|type=bak|src=EP}}xxxxxxxxxxxx{{PrérequisAtout|INT d8}}zzzzzzzz";
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
        $this->assertEquals('INT d8', $edge['Dummy']->getPrerequisite());
    }

    public function testCategoryList()
    {
        $cat = $this->sut->getAllEdgeCategory();
        $this->assertCount(1, $cat);
        $this->assertEquals('bak', array_key_first($cat));
    }

}
