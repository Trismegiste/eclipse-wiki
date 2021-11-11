<?php

/*
 * Eclipse Wiki
 */

use App\Entity\MediaWikiPage;
use App\Repository\MeleeWeaponProvider;
use PHPUnit\Framework\TestCase;
use Trismegiste\Toolbox\MongoDb\Repository;

/**
 * Unit test forr MeleeWeaponProvider
 */
class MeleeWeaponProviderTest extends TestCase
{

    protected $sut;

    protected function setUp(): void
    {
        $page = new MediaWikiPage('', '');
        $page->content = <<<EOF
{|
|-
!ARME BLANCHE
!DÉGÂTS
!AP
!FOR. MIN.
!POIDS kg
!COÛT
!NOTES
|-
|Katana
|FOR+d6+1
|
|d6
|1,5
|3
|2 mains
|}    
EOF;

        $pageRepo = $this->createMock(Repository::class);
        $pageRepo->expects($this->any())
            ->method('search')
            ->willReturn(new ArrayIterator([$page]));
        $this->sut = new MeleeWeaponProvider($pageRepo);
    }

    public function testFindAll()
    {
        $weapons = $this->sut->getListing();
        $this->assertCount(1, $weapons);
        $this->assertEquals('Katana', $weapons[0]->name);
    }

    public function testFindOne()
    {
        $weapon = $this->sut->findOne('Katana');
        $this->assertEquals('Katana', $weapon->name);
    }

}
