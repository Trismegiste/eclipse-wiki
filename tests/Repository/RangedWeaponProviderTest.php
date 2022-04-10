<?php

/*
 * Eclipse Wiki
 */

use App\Entity\MediaWikiPage;
use App\Repository\RangedWeaponProvider;
use PHPUnit\Framework\TestCase;
use Trismegiste\Strangelove\MongoDb\Repository;

/**
 * Description of RangedWeaponProviderTest
 */
class RangedWeaponProviderTest extends TestCase
{

    protected $sut;

    protected function setUp(): void
    {
        $page = new MediaWikiPage('', '');
        $page->content = <<<EOF
{|
|-
!'''MARQUE'''
!'''ARME À FEU'''
!'''PORTÉE'''
!'''DOM.'''
!'''AP'''
!'''CdT'''
!'''MAG.'''
!'''FOR'''
!'''COÛT'''
!'''ENC.'''
!'''M.'''
!'''T.'''
|-
|Seburo
|9mm
|12/24/48
|2d6
|2
|1
|10
|d4
|2
|2
|1
|C
|}    
EOF;

        $pageRepo = $this->createMock(Repository::class);
        $pageRepo->expects($this->any())
            ->method('search')
            ->willReturn(new ArrayIterator([$page]));
        $this->sut = new RangedWeaponProvider($pageRepo);
    }

    public function testFindAll()
    {
        $weapons = $this->sut->getListing();
        $this->assertCount(1, $weapons);
        $this->assertEquals('Seburo 9mm (C)', $weapons[0]->name);
    }

    public function testFindOne()
    {
        $weapon = $this->sut->findOne('Seburo 9mm (C)');
        $this->assertEquals('Seburo 9mm (C)', $weapon->name);
    }

}
