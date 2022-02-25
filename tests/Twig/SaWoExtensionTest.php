<?php

/*
 * eclipse-wiki
 */

use App\Twig\SaWoExtension;

class SaWoExtensionTest extends PHPUnit\Framework\TestCase
{

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
        $roll = new App\Entity\DamageRoll();
        $roll = $this->sut->addRaise($roll);
        $this->assertEquals(1, $roll->getDieCount(6));
        $roll = $this->sut->addRaise($roll);
        $this->assertEquals(2, $roll->getDieCount(6));
    }

    public function getNpc(): array
    {
        return [
            [new App\Entity\Ali('ali')],
            [new App\Entity\Freeform('free')]
        ];
    }

    /** @dataProvider getNpc */
    public function testIconForCharacter(App\Entity\Character $npc)
    {
        $this->assertStringStartsWith('icon', $this->sut->iconForCharacter($npc));
    }

    public function testBadCharIcon()
    {
        $this->expectException(OutOfBoundsException::class);
        $this->assertStringStartsWith('icon', $this->sut->iconForCharacter($this->createStub(App\Entity\Character::class)));
    }

}
