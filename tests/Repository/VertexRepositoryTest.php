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
        $this->assertEquals('one doc', $backlinked[0]);
    }

    public function testPrevious()
    {
        usleep(Import::delayForTimestamp); // delay to get two different typestamps
        $doc = new Scene('doc 2');
        $this->sut->save($doc);
        $pk = $doc->getPk();
        usleep(Import::delayForTimestamp); // delay to get two different typestamps
        $doc = new Scene('doc 3');
        $this->sut->save($doc);

        $found = $this->sut->searchPreviousOf($pk);
        $this->assertEquals('doc 3', $found->getTitle());
        $this->assertNull($this->sut->searchPreviousOf($doc->getPk()));

        return (string) $pk;
    }

    /** @depends testPrevious */
    public function testNext(string $pk)
    {
        $found = $this->sut->searchNextOf($pk);
        $this->assertEquals('one doc', $found->getTitle());
    }

    public function testRenameTitle()
    {
        $doc = new Scene('Backlink');
        $this->sut->save($doc);

        $modified = $this->sut->renameTitle('Backlink', 'Newlink');
        $this->assertEquals(2, $modified);

        // check backlink
        $changed = $this->sut->findByTitle('one doc');
        $this->assertEquals('some [[Newlink]].', $changed->getContent());
    }

    public function testFilterByContent()
    {
        $it = $this->sut->filterBy('some');
        $it->rewind();
        $obj = $it->current();
        $this->assertInstanceOf(Vertex::class, $obj);
        $this->assertEquals('one doc', $obj->getTitle());
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
        $obj->setContent('nihil');
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
        foreach($stat as $row) {
            if ($row->fqcn === Scene::class) {
                $this->assertEquals(6, $row->total);
                $this->assertEquals(1, $row->archived);
            }
        }
    }

}
