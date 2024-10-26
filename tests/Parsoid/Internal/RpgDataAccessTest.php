<?php

/*
 * eclipse-wiki
 */

use App\Entity\Scene;
use App\Parsoid\Internal\RpgDataAccess;
use App\Repository\VertexRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Trismegiste\Strangelove\MongoDb\Repository;

class RpgDataAccessTest extends KernelTestCase
{

    protected RpgDataAccess $sut;
    protected Repository $repo;

    protected function setUp(): void
    {
        static::bootKernel();
        $this->sut = static::getContainer()->get(RpgDataAccess::class);
        $this->repo = static::getContainer()->get(VertexRepository::class);
    }

    public function testClean()
    {
        $this->repo->delete(iterator_to_array($this->repo->search()));
        $this->assertCount(0, iterator_to_array($this->repo->search()));
    }

    public function testPageInfo()
    {
        $scene = new Scene('Jupiter');
        $this->repo->save($scene);

        $ret = $this->sut->getPageInfo('dummy', ['jupiter', 'Jupiter', 'Mars']);
        $this->assertCount(3, $ret);
        $this->assertNotNull($ret['Jupiter']['pageId']);
        $this->assertNull($ret['Mars']['pageId']);
        $this->assertNotNull($ret['jupiter']['pageId']);
    }

}
