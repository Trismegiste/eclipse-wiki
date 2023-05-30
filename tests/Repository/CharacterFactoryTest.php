<?php

/*
 * Eclipse Wiki
 */

use App\Repository\CharacterFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CharacterFactoryTest extends KernelTestCase
{

    protected CharacterFactory $sut;

    protected function setUp(): void
    {
        $this->sut = static::getContainer()->get(CharacterFactory::class);
    }

    public function testTranshuman()
    {
        $bg = $this->createStub(\App\Entity\Background::class);
        $fac = $this->createStub(\App\Entity\Faction::class);
        $npc = $this->sut->create('Takeshi', $bg, $fac);
        $this->assertInstanceOf(\App\Entity\Transhuman::class, $npc);
        $this->assertCount(5, $npc->attributes);
    }

}
