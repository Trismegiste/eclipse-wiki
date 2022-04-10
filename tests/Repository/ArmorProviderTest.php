<?php

/*
 * Eclipse Wiki
 */

use App\Entity\MediaWikiPage;
use App\Repository\ArmorProvider;
use PHPUnit\Framework\TestCase;
use Trismegiste\Strangelove\MongoDb\Repository;

/**
 * Unit test for ArmorProvider
 */
class ArmorProviderTest extends TestCase
{

    protected $sut;

    protected function setUp(): void
    {
        $page = new MediaWikiPage('', '');
        $page->content = <<<EOF
{|
|-
!'''ARMURE'''
!'''PROT.'''
!'''SPÉ.'''
!'''LOC.'''
!'''FOR.'''
!'''ENC.'''
!'''COÛT'''
|-
|Yolo
|2
|
|T/B/J
|d4
|2
|2
|}
EOF;

        $pageRepo = $this->createMock(Repository::class);
        $pageRepo->expects($this->any())
            ->method('search')
            ->willReturn(new ArrayIterator([$page]));
        $this->sut = new ArmorProvider($pageRepo);
    }

    public function testFindAll()
    {
        $weapons = $this->sut->getListing();
        $this->assertCount(1, $weapons);
        $this->assertEquals('Yolo', $weapons[0]->name);
    }

    public function testFindOne()
    {
        $weapon = $this->sut->findOne('Yolo');
        $this->assertEquals('Yolo', $weapon->name);
    }

}
