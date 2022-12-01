<?php

use App\Entity\Scene;
use App\Entity\Vertex;
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

    protected function buildScene(string $str): Scene
    {
        $obj = new Scene($str);
        $obj->setContent('content' . rand());

        return $obj;
    }

    public function testOneVertex()
    {
        $scene = $this->buildScene('Racine');
        $this->repository->save($scene);
        $matrix = $this->sut->getAdjacencyMatrix();
        $this->assertCount(1, $matrix);
        $pk = (string) $scene->getPk();
        $this->assertArrayHasKey($pk, $matrix);
        $this->assertArrayHasKey($pk, $matrix[$pk]);
        $this->assertFalse($matrix[$pk][$pk]);

        return $scene;
    }

    /** @depends testOneVertex */
    public function testSelfReference(scene $scene)
    {
        $pk = (string) $scene->getPk();
        $scene->setContent('[[Racine]]');
        $this->repository->save($scene);
        $matrix = $this->sut->getAdjacencyMatrix();
        $this->assertCount(1, $matrix);
        $this->assertTrue($matrix[$pk][$pk]);
    }

    protected function assertLinksCount(int $n, array $matrix): void
    {
        $ct = 0;
        foreach ($matrix as $row) {
            foreach ($row as $link) {
                if ($link) {
                    $ct++;
                }
            }
        }

        $this->assertEquals($n, $ct);
    }

    public function testOneLeafToRoot()
    {
        $scene = $this->buildScene('Leaf1');
        $scene->setContent('[[Racine]]');
        $this->repository->save($scene);

        $matrix = $this->sut->getAdjacencyMatrix();
        $this->assertCount(2, $matrix);
        $pk = (string) $scene->getPk();
        $this->assertFalse($matrix[$pk][$pk]);
        $root = $this->repository->findByTitle('Racine');
        $this->assertFalse($matrix[(string) $root->getPk()][$pk]);
        $this->assertTrue($matrix[$pk][(string) $root->getPk()]);
    }

    public function testLeafToLeafWithLowercase()
    {
        $scene = $this->buildScene('Leaf2');
        $scene->setContent('[[leaf1]]');
        $this->repository->save($scene);

        $matrix = $this->sut->getAdjacencyMatrix();
        $this->assertCount(3, $matrix);
        $this->assertLinksCount(3, $matrix);
    }

    public function testClique()
    {
        // reset repo
        $this->repository->delete(iterator_to_array($this->repository->search()));
        $this->assertCount(0, $this->repository->search());

        // insert a 5-clique
        for ($k = 0; $k < 5; $k++) {
            $vertex[$k] = new Scene('Scene' . $k);
            $content = '';
            for ($target = 0; $target < 5; $target++) {
                if ($k !== $target) {
                    $content .= "* Link to [[scene$target]]\n";
                }
            }
            $vertex[$k]->setContent($content);
        }
        $this->repository->save($vertex);

        $matrix = $this->sut->getAdjacencyMatrix();
        $this->assertCount(5, $matrix);
        $this->assertLinksCount(20, $matrix);
    }

    public function testNoOrphan()
    {
        $orphan = $this->sut->findOrphan();
        $this->assertCount(0, $orphan);
    }

    public function testAddOrphan()
    {
        $orphan = $this->buildScene('Orphan');
        $orphan->setContent('[[orphan]] [[ThisVertexDoesNotExist]]'); // $orphan is an orphan since it is only linking to itself and a vertex that does not exist
        $this->repository->save($orphan);
        $matrix = $this->sut->getAdjacencyMatrix();
        $this->assertCount(6, $matrix);
        $this->assertLinksCount(21, $matrix);

        $listing = $this->sut->findOrphan();
        $this->assertCount(1, $listing);

        return $orphan;
    }

    /** @depends testAddOrphan */
    public function testNotOrphanWithBacklinks(Vertex $orphan)
    {
        $orphan->setContent($orphan->getContent() . "[[not orphan]]");
        $this->repository->save($orphan);
        $matrix = $this->sut->getAdjacencyMatrix();
        $this->assertCount(6, $matrix);
        $this->assertLinksCount(21, $matrix);
        $this->assertCount(1, $this->sut->findOrphan());

        $child = $this->buildScene('Not orphan');
        $this->repository->save($child);

        $matrix = $this->sut->getAdjacencyMatrix();
        $this->assertCount(7, $matrix);
        $this->assertLinksCount(22, $matrix);

        $this->assertCount(0, $this->sut->findOrphan());
    }

    public function testBrokenLink()
    {
        $found = $this->sut->searchForBrokenLink();
        $this->assertCount(1, $found);
        $this->assertArrayHasKey('ThisVertexDoesNotExist', $found);
        $this->assertEquals(['Orphan' => true], $found['ThisVertexDoesNotExist']);
    }

}
