<?php

/*
 * eclipse-wiki
 */

use App\Entity\Attribute;
use App\Entity\TraitBonus;
use App\Entity\TraitRoll;
use PHPUnit\Framework\TestCase;

/**
 * Description of TraitRollTest
 *
 * @author trismegiste
 */
class TraitRollTest extends TestCase
{

    public function getTrait(): array
    {
        return [
            [8, 0, 0, 0, 8, 0], // d8 = d8
            [8, 0, 2, 0, 12, 0], // d8 + 2TDD = d12
            [12, 0, 2, 0, 12, 2], // d12 + 2TDD = d12+2
            [12, 1, 1, 0, 12, 2], // d12 + 1 + 1TDD = d12+2
            [8, 2, 1, 0, 10, 2], // d8+2 + 1TDD = d10 + 2 and NOT d12+1
            [8, 0, 0, 3, 8, 3], // d8+0 + 3 = d8+3 and NOT d12+1
            [4, 0, 5, 0, 12, 1], // d4 + 5TDD = d12+1
            [4, 0, 4, 0, 12, 0], // d4 + 4TDD = d12
        ];
    }

    /** @dataProvider getTrait */
    public function testRolling(int $side, int $mod12, int $bonusSide, int $flat, int $sideResult, int $flatResult)
    {
        $attr = new Attribute('TST');
        $attr->dice = $side;
        $attr->modifier = $mod12;
        $bonus = new TraitBonus($bonusSide, $flat);

        $sut = new TraitRoll($attr, $bonus);

        $this->assertEquals($sideResult, $sut->getSide());
        $this->assertEquals($flatResult, $sut->getModifier());
    }

    /** @dataProvider getTrait */
    public function testNoBonus(int $side, int $mod12, int $bonusSide, int $flat, int $sideResult, int $flatResult)
    {
        $attr = new Attribute('TST');
        $attr->dice = $side;
        $attr->modifier = $mod12;
        $sut = new TraitRoll($attr);

        $this->assertEquals($side, $sut->getSide());
        $this->assertEquals($mod12, $sut->getModifier());
    }

}
