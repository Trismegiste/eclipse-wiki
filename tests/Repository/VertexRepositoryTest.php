<?php

/*
 * Vesta
 */

use App\Entity\Vertex;
use App\Repository\VertexRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class VertexRepositoryTest extends KernelTestCase
{

    protected $sut;

    protected function setUp(): void
    {
        static::createKernel();
        $this->sut = static::getContainer()->get(VertexRepository::class);
    }

    public function testBacklinks()
    {
        $doc = new Vertex('one doc');
        $doc->setContent('some [[backlink]].');
        $this->sut->save($doc);

        $backlinked = $this->sut->searchByBacklinks('Backlink');
        $this->assertIsArray($backlinked);
        $this->assertCount(1, $backlinked);
        $this->assertEquals('one doc', $backlinked[0]);
    }

    public function testPrevious()
    {
        $doc = new Vertex('doc 2');
        $this->sut->save($doc);
        $pk = $doc->getPk();
        $doc = new Vertex('doc 3');
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
        $doc = new Vertex('Backlink');
        $this->sut->save($doc);

        $modified = $this->sut->renameTitle('Backlink', 'Newlink');
        $this->assertEquals(2, $modified);

        // check backlink
        $changed = $this->sut->findByTitle('one doc');
        $this->assertEquals('some [[Newlink]].', $changed->getContent());
    }

}
