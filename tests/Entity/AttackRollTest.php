<?php

/*
 * eclipse-wiki
 */

use App\Entity\Attack;
use App\Entity\AttackRoll;
use App\Entity\Skill;
use App\Entity\TraitBonus;
use App\Entity\TraitRoll;
use App\Entity\DamageRoll;
use PHPUnit\Framework\TestCase;

class AttackRollTest extends TestCase
{

    public function testProperties()
    {
        $attack = new Attack();
        $attack->title = 'Fist';
        $attack->roll = new Skill('Fight', 'AGI');
        $attack->roll->dice = 10;
        $attack->reach = 'melee';
        $attack->damage = DamageRoll::createFromString('2d6');

        $roll = new AttackRoll($attack, new TraitBonus(1));

        $this->assertEquals('Fist', $roll->getTitle());
        $this->assertEquals('Fight', $roll->getLabel());
        $this->assertEquals(12, $roll->getDice());
        $this->assertEquals(0, $roll->getModifier());
        $this->assertInstanceOf(TraitRoll::class, $roll->getRoll());
        $this->assertEquals('melee', $roll->getReach());
        $this->assertEquals(0, $roll->getArmorPiercing());
        $this->assertEquals(1, $roll->getRateOfFire());
        $this->assertEquals('2d6', $roll->getDamage());
    }

}
