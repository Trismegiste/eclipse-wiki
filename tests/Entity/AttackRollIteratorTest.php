<?php

/*
 * eclipse-wiki
 */

use App\Entity\Attack;
use App\Entity\AttackRoll;
use App\Entity\AttackRollIterator;
use App\Entity\Morph;
use App\Entity\Skill;
use PHPUnit\Framework\TestCase;

class AttackRollIteratorTest extends TestCase
{

    protected $sut;
    protected $morph;

    protected function setUp(): void
    {
        $this->morph = new Morph('Yolo');
        $attack = new Attack();
        $attack->roll = new Skill('Fight', 'AGI');

        $this->sut = new AttackRollIterator([$attack], $this->morph);
    }

    public function testCount()
    {
        $this->assertCount(1, $this->sut);
    }

    public function testType()
    {
        foreach ($this->sut as $roll) {
            $this->assertInstanceOf(AttackRoll::class, $roll);
        }
    }

}
