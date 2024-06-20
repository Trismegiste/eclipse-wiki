<?php

use App\Entity\Freeform;
use App\Entity\Place;
use App\Entity\PlotNode;
use App\Entity\Scene;
use App\Entity\Timeline;
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

    public function testConnectedPlace()
    {
        $level1 = new Place('level1');
        $level1->setContent('[[level2]]');
        $level2 = new Place('level2');
        $level2->setContent('[[level3]]');
        $level3 = new Place('level3');
        $this->repository->save([$level1, $level2, $level3]);

        $this->assertCount(1, $this->sut->searchForConnectedPlace($level1));
        $this->assertCount(2, $this->sut->searchForConnectedPlace($level2));
        $this->assertCount(1, $this->sut->searchForConnectedPlace($level3));
    }

    public function testPartitionRealCase()
    {
        // reset repo
        $this->repository->delete(iterator_to_array($this->repository->search()));
        $this->assertCount(0, $this->repository->search());

        $root = new Timeline('A new hope');
        $root->elevatorPitch = 'It begins';
        $root->setTree(new PlotNode('root', [new PlotNode('[[scene1]]')]));
        $scene1 = new Scene('scene1');
        $scene1->setContent('[[Luke]] on [[Tatooine]]');
        $place = new Place('Tatooine');
        $place->setContent('Desert');
        $char1 = new Freeform('C3PO');
        $char2 = new Freeform('Luke');
        $char2->setContent('Buying [[C3PO]] and [[R2D2]]');

        $spinoff = new Timeline('Rogue One');
        $spinoff->elevatorPitch = 'the plans';
        $spinoff->setTree(new PlotNode('root', [new PlotNode('[[scene5]]')]));
        $scene5 = new Scene('scene5');
        $scene5->setContent('Cameo of [[C3PO]] on [[Yavin 4]]');

        $orphan = new Timeline('Ep9');
        $orphan->elevatorPitch = 'trash';
        $orphan->setTree(new PlotNode('root'));

        $this->repository->save([$root, $spinoff, $char1, $char2, $scene1, $scene5, $place, $orphan]);
        $partition = $this->sut->getPartitionByTimeline();
        $this->assertArrayHasKey('A new hope', $partition);
        $this->assertArrayHasKey('Rogue One', $partition);
        $this->assertArrayHasKey('Ep9', $partition);
        $this->assertCount(3, $partition['A new hope']);
        $this->assertCount(2, $partition['Rogue One']);
        $this->assertCount(0, $partition['Ep9']);

        $this->assertEquals(['Luke', 'Scene1', 'Tatooine'], array_column($partition['A new hope'], 'title'));
        $this->assertEquals(['C3PO', 'Scene5'], array_column($partition['Rogue One'], 'title'));

        return $root;
    }

    /** @depends testPartitionRealCase */
    public function testBrokenByTimeline(Timeline $root)
    {
        $listing = $this->sut->searchForBrokenLinkByTimeline($root);
        $this->assertCount(1, $listing);
        $this->assertArrayHasKey('R2D2', $listing);
        $this->assertEquals(['Luke' => true], $listing['R2D2']);
    }

    public function testSortedCentrality()
    {
        $center = new Timeline('center');
        $center->elevatorPitch = 'summary';
        $vertex = [$center];
        $tree = new PlotNode('root');
        for ($k = 0; $k < 5; $k++) {
            $tree->nodes[] = new PlotNode("[[scene $k]]");
            $vertex[] = new Scene("scene $k");
        }
        $center->setTree($tree);

        $this->repository->save($vertex);
        $result = $this->sut->getVertexSortedByCentrality($center);

        $this->assertCount(6, $result);
        $this->assertEquals('Center', $result[0]->title);
        // the path from each 5 scenes to the 4 other scenes need to get through the timeline
        // therefore, the timeline has a betweenness of 20
        $this->assertEquals(20.0, $result[0]->betweenness);
    }

}
