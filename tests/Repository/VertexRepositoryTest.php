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

}
