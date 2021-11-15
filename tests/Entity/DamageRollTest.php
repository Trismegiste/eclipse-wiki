<?php

/*
 * eclipse-wiki
 */

use App\Entity\DamageRoll;
use PHPUnit\Framework\TestCase;

class DamageRollTest extends TestCase
{

    protected $sut;

    protected function setUp(): void
    {
        $this->sut = new DamageRoll();
    }

    public function testParsingBonus()
    {
        $obj = DamageRoll::createFromString('  4  ');
        $this->assertEquals(4, $obj->getBonus());
    }

    public function testParsingOneDie()
    {
        $obj = DamageRoll::createFromString('  1d4  ');
        $this->assertEquals(1, $obj->getDieCount(4));
    }

    public function testParsingDiceWithBonus()
    {
        $obj = DamageRoll::createFromString('2d4+3d6+4');
        $this->assertEquals(2, $obj->getDieCount(4));
        $this->assertEquals(3, $obj->getDieCount(6));
        $this->assertEquals(4, $obj->getBonus());
    }

    public function testParsingAddingSameDie()
    {
        $obj = DamageRoll::createFromString('  1d4 +1d4');
        $this->assertEquals(2, $obj->getDieCount(4));
    }

    public function testParsingMultipleSameDie()
    {
        $obj = DamageRoll::createFromString('  2d4');
        $this->assertEquals(2, $obj->getDieCount(4));
    }

    public function testParsingAddingMultipleSameDie()
    {
        $obj = DamageRoll::createFromString('  2d4 +1d4+ 1d4 ');
        $this->assertEquals(4, $obj->getDieCount(4));
    }

    public function testParsingAllDice()
    {
        $obj = DamageRoll::createFromString('  1d4+1d6+1d8 + 1d10+1d12 ');
        for ($k = 4; $k <= 12; $k += 2) {
            $this->assertEquals(1, $obj->getDieCount($k));
        }
    }

    public function testAddDie()
    {
        $this->sut->addDice(12, 4);
        $this->assertEquals(4, $this->sut->getDieCount(12));
        $this->assertEquals([4 => 0, 6 => 0, 8 => 0, 10 => 0, 12 => 4], $this->sut->getDiceCount());
    }

    public function testToString()
    {
        $this->sut->addDice(4, 1);
        $this->assertEquals('1d4', (string) $this->sut);
        $this->sut->addDice(10, 5);
        $this->assertEquals('1d4+5d10', (string) $this->sut);
        $this->sut->incBonus(3);
        $this->assertEquals('1d4+5d10+3', (string) $this->sut);
    }

    public function testJson()
    {
        $this->assertJson(json_encode($this->sut));
    }

}
