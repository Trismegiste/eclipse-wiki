<?php

/*
 * eclipse-wiki
 */

use App\Entity\Ali;
use App\Entity\Background;
use App\Entity\Character;
use App\Entity\DamageRoll;
use App\Entity\Faction;
use App\Entity\Freeform;
use App\Entity\Transhuman;
use App\Entity\Vertex;
use App\Twig\SaWoExtension;
use PHPUnit\Framework\TestCase;

class SaWoExtensionTest extends TestCase
{

    use \App\Tests\Controller\PictureFixture;

    protected $sut;

    protected function setUp(): void
    {
        $this->sut = new SaWoExtension();
    }

    public function testGetFunctions()
    {
        $this->assertIsArray($this->sut->getFunctions());
    }

    public function testDiceIcon()
    {
        $this->assertEquals('<i class="icon-d4"></i>', $this->sut->diceIcon('d4'));
        $this->assertEquals('<i class="icon-d8"></i>', $this->sut->diceIcon('d8'));
        $this->assertEquals('<i class="icon-d12"></i>', $this->sut->diceIcon('d12'));
        $this->assertEquals('<i class="icon-d12"></i>+2', $this->sut->diceIcon('d12+2'));
    }

    public function testBadDiceIcon()
    {
        $this->assertEquals('666', $this->sut->diceIcon('666'));
    }

    public function testPrintLevelHindrance()
    {
        $this->assertEquals('M/m', $this->sut->printLevelHindrance(3));
    }

    public function testAddRaise()
    {
        $roll = new DamageRoll();
        $roll = $this->sut->addRaise($roll);
        $this->assertEquals(1, $roll->getDieCount(6));
        $roll = $this->sut->addRaise($roll);
        $this->assertEquals(2, $roll->getDieCount(6));
    }

    public function getVertex(): array
    {
        return [
            [new Ali('ali'), 'icon-ali'],
            [new Freeform('free'), 'icon-monster'],
            [$this->createRandomTranshuman(), 'icon-male'],
            [$this->createRandomTranshuman(wildcard: true), 'icon-wildcard'],
            [$this->createRandomTranshuman(extra: true), 'icon-extra'],
            [$this->createRandomPlace(), 'icon-place'],
            [$this->createRandomPlace('Simulespace'), 'icon-simulspace'],
            [$this->createRandomTimeline(), 'icon-movie-roll'],
            [$this->createRandomScene(), 'icon-video'],
            [$this->createRandomHandout(), 'icon-handout'],
            [$this->createRandomLoveletter(), 'icon-loveletter'],
        ];
    }

    /** @dataProvider getVertex */
    public function testIconForVertex(Vertex $npc, $expected)
    {
        $icon = $this->sut->iconForVertex($npc);
        $this->assertEquals($expected, $icon);
    }

    public function testDefaultIcon()
    {
        $this->assertEquals('icon-graph', $this->sut->iconForVertex($this->createStub(Character::class)));
    }

}
