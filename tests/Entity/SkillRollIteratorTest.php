<?php

/*
 * eclipse-wiki
 */

use App\Entity\Morph;
use App\Entity\Skill;
use App\Entity\SkillRollIterator;
use App\Entity\TraitRoll;
use PHPUnit\Framework\TestCase;

class SkillRollIteratorTest extends TestCase
{

    protected $sut;
    protected $morph;

    protected function setUp(): void
    {
        $this->morph = new Morph('Yolo');
        $skill = new Skill('Fight', 'AGI');
        $skill->dice = 10;
        $this->sut = new SkillRollIterator([$skill], $this->morph);
    }

    public function testCount()
    {
        $this->assertCount(1, $this->sut);
    }

    public function testType()
    {
        foreach ($this->sut as $roll) {
            $this->assertInstanceOf(TraitRoll::class, $roll);
        }
    }

    public function testBonus()
    {
        $this->morph->skillBonus['Fight'] = new \App\Entity\TraitBonus(1);
        foreach ($this->sut as $roll) {
            $this->assertInstanceOf(TraitRoll::class, $roll);
            $this->assertEquals(12, $roll->getDice());
            $this->assertTrue($roll->isAltered());
            $this->assertEquals('Fight', $roll->getLabel());
        }
    }

}
