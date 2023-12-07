<?php

/*
 * eclipse-wiki
 */

use App\Entity\Scene;
use App\Repository\VertexRepository;
use App\Service\DigraphExplore;
use App\Service\InfoDashboard;
use App\Service\Storage;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Cache\Adapter\NullAdapter;

class InfoDashboardTest extends KernelTestCase
{

    protected InfoDashboard $sut;
    protected VertexRepository $repository;

    protected function setUp(): void
    {
        $this->repository = static::getContainer()->get(VertexRepository::class);
        $this->sut = new InfoDashboard(
                new NullAdapter(),
                $this->repository,
                static::getContainer()->get(DigraphExplore::class),
                static::getContainer()->get(Storage::class)
        );
    }

    public function testClean()
    {
        $this->repository->delete(iterator_to_array($this->repository->search()));
        $this->assertCount(0, $this->repository->search());
    }

    public function testVertexCount()
    {
        $vertex = new Scene('scene1');
        $vertex->setContent('[[missing]] [[file:missing.jpg]]');
        $this->repository->save($vertex);

        $this->assertEquals(1, $this->sut->getVertexCount());
    }

    public function testBrokenLink()
    {
        $this->assertEquals(1, $this->sut->getBrokenLinkCount());
    }

    public function testBrokenPicture()
    {
        $this->assertEquals(1, $this->sut->getBrokenPictureCount());
    }

    public function testOrphan()
    {
        $this->assertEquals(1, $this->sut->getOrphanCount());
    }

}
