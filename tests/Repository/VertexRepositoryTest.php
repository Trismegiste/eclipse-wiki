<?php

/*
 * Vesta
 */

use App\Command\Import;
use App\Entity\Background;
use App\Entity\Faction;
use App\Entity\Freeform;
use App\Entity\Scene;
use App\Entity\Timeline;
use App\Entity\Transhuman;
use App\Entity\Vertex;
use App\Repository\VertexRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class VertexRepositoryTest extends KernelTestCase
{

    protected VertexRepository $sut;

    protected function setUp(): void
    {
        static::createKernel();
        $this->sut = static::getContainer()->get(VertexRepository::class);
    }

    public function testReset()
    {
        $this->sut->delete(iterator_to_array($this->sut->search()));
        $this->assertCount(0, $this->sut->search());
    }

    public function testBacklinks()
    {
        $doc = new Scene('one doc');
        $doc->setContent('some [[backlink]].');
        $this->sut->save($doc);

        $backlinked = $this->sut->searchByBacklinks('Backlink');
        $this->assertIsArray($backlinked);
        $this->assertCount(1, $backlinked);
        $this->assertEquals('One doc', $backlinked[0]);

        $backlinked = $this->sut->searchByBacklinks('backlink');
        $this->assertCount(1, $backlinked);
        $this->assertEquals('One doc', $backlinked[0]);
    }

    public function testPrevious()
    {
        usleep(Import::delayForTimestamp); // delay to get two different typestamps
        $doc = new Scene('doc 2');
        $this->sut->save($doc);
        $pk = $doc->getPk();
        usleep(Import::delayForTimestamp); // delay to get two different typestamps
        $doc = new Scene('Doc 3');
        $this->sut->save($doc);

        $found = $this->sut->searchPreviousOf($pk);
        $this->assertEquals('Doc 3', $found->getTitle());
        $this->assertNull($this->sut->searchPreviousOf($doc->getPk()));

        return (string) $pk;
    }

    /** @depends testPrevious */
    public function testNext(string $pk)
    {
        $found = $this->sut->searchNextOf($pk);
        $this->assertEquals('One doc', $found->getTitle());
    }

    public function testRenameTitle()
    {
        $doc = new Scene('Backlink');
        $this->sut->save($doc);

        $modified = $this->sut->renameTitle('Backlink', 'Newlink');
        $this->assertEquals(2, $modified);

        // check backlink
        $changed = $this->sut->findByTitle('one doc');
        $this->assertEquals('One doc', $changed->getTitle());
        $this->assertEquals('some [[Newlink]].', $changed->getContent());
    }

    public function testFilterByContent()
    {
        $it = $this->sut->filterBy('some');
        $it->rewind();
        $obj = $it->current();
        $this->assertInstanceOf(Vertex::class, $obj);
        $this->assertEquals('One doc', $obj->getTitle());
    }

    public function testFilterEmpty()
    {
        $it = $this->sut->filterBy('wxzwxzwxz');
        $this->assertEquals([], iterator_to_array($it));
    }

    public function testSearchNpcByTokenEmpty()
    {
        $this->assertCount(0, $this->sut->searchNpcWithToken());

        $npc = new Freeform('monster');
        $npc->tokenPic = 'monster.png';
        $this->sut->save($npc);
    }

    /** @depends testSearchNpcByTokenEmpty */
    public function testSearchNpcByTokenOne()
    {
        $this->assertCount(1, $this->sut->searchNpcWithToken());
    }

    public function testExploreOrphanTimeline()
    {
        $obj = new Timeline('Root');
        $obj->setTree(new \App\Entity\PlotNode('root'));
        $obj->elevatorPitch = 'nihil';
        $this->sut->save($obj);

        $res = $this->sut->exploreTreeFrom($obj);
        $this->assertCount(1, $res);
        $this->assertArrayHasKey('Root', $res);
        $this->assertInstanceOf(Timeline::class, $res['Root']);

        return $obj;
    }

    /** @depends testExploreOrphanTimeline */
    public function testExploreUniqueSceneLinkedToTimeline(Timeline $root)
    {
        $obj = new Scene('Intro');
        $obj->setContent('Part of [[Root]]');
        $this->sut->save($obj);

        $res = $this->sut->exploreTreeFrom($root);
        $this->assertCount(2, $res);
        $this->assertArrayHasKey('Root', $res);
        $this->assertArrayHasKey('Intro', $res);
        $this->assertInstanceOf(Scene::class, $res['Intro']);

        return $root;
    }

    /** @depends testExploreUniqueSceneLinkedToTimeline */
    public function testFriendsOfFriends(Timeline $root)
    {
        $npc = new Transhuman('Antagonist', new Background('nihil'), new Faction('dummy'));
        $obj = new Scene('Fight');
        $obj->setContent('Fight with [[Antagonist]] - Part of [[Root]]');
        $this->sut->save([$npc, $obj]);

        $res = $this->sut->exploreTreeFrom($root);
        $this->assertCount(4, $res);
        $this->assertArrayHasKey('Antagonist', $res);
        $this->assertArrayHasKey('Fight', $res);

        return $obj;
    }

    /** @depends testFriendsOfFriends */
    public function testStatistics(Scene $scene)
    {
        $scene->setArchived(true);
        $this->sut->save($scene);
        $res = $this->sut->countByClass();
        $stat = iterator_to_array($res);
        foreach ($stat as $row) {
            if ($row->fqcn === Scene::class) {
                $this->assertEquals(6, $row->total);
                $this->assertEquals(1, $row->archived);
            }
        }
    }

    public function testInternalsLinks()
    {
        $iter = $this->sut->dumpAllInternalLinks();
        $edges = iterator_to_array($iter);
        var_dump($edges);
        $this->assertCount(4, $edges);
    }

    public function testAdjacencyMatrix()
    {
        $matrix = $this->sut->getAdjacencyMatrix();
        $this->assertCount(9, $matrix);

        $edgeCount = 0;
        foreach ($matrix as $row) {
            foreach ($row as $cnx) {
                if ($cnx) {
                    $edgeCount++;
                }
            }
        }

        $this->assertEquals(4, $edgeCount);
    }

    public function testFirstLetterCaseInsensitive()
    {
        $vertex = new Freeform('kArEn');
        $this->sut->save($vertex);
        $this->assertNotNull($this->sut->findByTitle('kArEn'));
        $this->assertNull($this->sut->findByTitle('karen'));
        $this->assertNotNull($this->sut->findByTitle('KArEn'));
        $this->assertNull($this->sut->findByTitle('KAREN'));
    }

    public function testRenameComplexContent()
    {
        $inbound = new Freeform('Starkiller');
        $handout = new App\Entity\Handout('BG of Jedi');
        $handout->pcInfo = 'Link to [[Starkiller|Luke]]';
        $handout->gmInfo = 'Link to [[Starkiller|starkiller family]]';
        $loveletter = new App\Entity\Loveletter('Buy some droids');
        $loveletter->context = '[[Starkiller|Luke]] lived in a farm';
        $loveletter->drama = '[[Starkiller|Luke]] must purchase some droids';
        $timeline = new Timeline('A new hope');
        $timeline->elevatorPitch = 'Space opera';
        $timeline->setTree(new \App\Entity\PlotNode('root', [new \App\Entity\PlotNode('Starting with [[Starkiller|Luke]]')]));
        $bunch = [$inbound, $handout, $loveletter, $timeline];
        $this->sut->save($bunch);

        $modif = $this->sut->renameTitle('starkiller', 'Skywalker');
        $this->assertEquals(4, $modif);

        // just to be sure we lost original objects, sending only the primary keys
        return array_map(function (Vertex $obj) {
            return $obj->getPk();
        }, $bunch);
    }

    /** @depends testRenameComplexContent */
    public function testRenameIsCorrect(array $pk)
    {
        $vertex = array_map(function ($i) {
            return $this->sut->load($i);
        }, $pk);
        list($inbound, $handout, $loveletter, $timeline) = $vertex;
        // inbound
        $this->assertEquals('Skywalker', $inbound->getTitle());
        // handout
        $this->assertEquals('Link to [[Skywalker|Luke]]', $handout->pcInfo);
        $this->assertEquals('Link to [[Skywalker|starkiller family]]', $handout->gmInfo);
        // loveletter
        $this->assertEquals('[[Skywalker|Luke]] lived in a farm', $loveletter->context);
        $this->assertEquals('[[Skywalker|Luke]] must purchase some droids', $loveletter->drama);
        /** @var App\Entity\Timeline $timeline */
        $this->assertEquals('Starting with [[Skywalker|Luke]]', $timeline->getTree()[0]->title);
    }

}
