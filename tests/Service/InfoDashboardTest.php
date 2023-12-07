<?php

/*
 * eclipse-wiki
 */

use App\Repository\VertexRepository;
use App\Service\InfoDashboard;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class InfoDashboardTest extends KernelTestCase
{

    protected InfoDashboard $sut;
    protected VertexRepository $repository;

    protected function setUp(): void
    {
        $this->sut = static::getContainer()->get(InfoDashboard::class);
        $this->repository = static::getContainer()->get(VertexRepository::class);
    }

    public function testClean()
    {
        $this->repository->delete(iterator_to_array($this->repository->search()));
        $this->assertCount(0, $this->repository->search());
    }

    public function testVertexCount()
    {
        $vertex = new App\Entity\Scene('scene1');
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
