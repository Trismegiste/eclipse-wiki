<?php

use App\Entity\Scene;
use App\Repository\VertexRepository;
use App\Service\DigraphExplore;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DigraphExploreTest extends KernelTestCase
{

    protected DigraphExplore $sut;
    protected VertexRepository $repository;

    protected function setUp(): void
    {
        $this->sut = static::getContainer()->get(DigraphExplore::class);
        $this->repository = static::getContainer()->get(VertexRepository::class);
    }

    public function testClean()
    {
        $this->repository->delete(iterator_to_array($this->repository->search()));
        $this->assertCount(0, $this->repository->search());
    }

    public function testEmptyAdjacencyMatrix()
    {
        $this->assertCount(0, $this->sut->getAdjacencyMatrix());
    }

    protected function buildRandomScene(): Scene
    {
        $obj = new Scene('title' . rand());
        $obj->setContent('content' . rand());

        return $obj;
    }

    public function testOneOrphanAdjacencyMatrix()
    {
        $scene = $this->buildRandomScene();
        $this->repository->save($scene);
        $matrix = $this->sut->getAdjacencyMatrix();
        $this->assertCount(1, $matrix);
        $pk = (string) $scene->getPk();
        $this->assertArrayHasKey($pk, $matrix);
        $this->assertArrayHasKey($pk, $matrix[$pk]);
        $this->assertFalse($matrix[$pk][$pk]);

        return $scene;
    }

    /** @depends testOneOrphanAdjacencyMatrix */
    public function testSelfReferenceAdjacencyMatrix(scene $scene)
    {
        $pk = (string) $scene->getPk();
        $scene->setContent('[[' . $scene->getTitle() . ']]');
        $this->repository->save($scene);
        $matrix = $this->sut->getAdjacencyMatrix();
        $this->assertTrue($matrix[$pk][$pk]);
    }

}
