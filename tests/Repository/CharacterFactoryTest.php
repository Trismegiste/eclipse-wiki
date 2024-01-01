<?php

/*
 * Eclipse Wiki
 */

use App\Entity\Background;
use App\Entity\Faction;
use App\Entity\Transhuman;
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
        $bg = $this->createStub(Background::class);
        $fac = $this->createStub(Faction::class);
        $npc = $this->sut->create('Diplo', $bg, $fac);
        $this->assertInstanceOf(Transhuman::class, $npc);
        $this->assertCount(5, $npc->attributes);

        return $npc;
    }

    /** @depends testTranshuman */
    public function testCreateFromTemplate(Transhuman $template)
    {
        $template->setContent('yolo');
        $created = $this->sut->createExtraFromTemplate($template, 'Takeshi');
        $this->assertInstanceOf(Transhuman::class, $created);
        $this->assertEquals('Diplo', $created->instantiatedFrom);
        $this->assertStringContainsString('[[Diplo]]', $created->getContent());
    }

}
